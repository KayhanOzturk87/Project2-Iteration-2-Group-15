<?php
require_once __DIR__ . '/../config/db_connect.php';

session_start();
header('Content-Type: application/json');

$apiKey = "AIzaSyA0BIobaaomvRzQca-bv5LWu3P2SY8OgVQ";

if (!$apiKey) {
    error_log("Gemini API Key is not set.");
    echo json_encode(['reply' => 'Configuration error: Assistant is currently unavailable. (API Key Missing)']);
    exit();
}

$base_url = '/';

function call_gemini_api_with_curl($apiKey, $prompt_text) {
    $gemini_api_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=' . $apiKey;

    $post_data = json_encode([
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt_text]
                ]
            ]
        ],
     
    ]);

    $ch = curl_init($gemini_api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
    ]);
    // curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
    // curl_setopt($ch, CURLOPT_TIMEOUT, 30);

    $response_json = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error_num = curl_errno($ch);
    $curl_error_msg = curl_error($ch);
    curl_close($ch);

    if ($curl_error_num > 0) {
        error_log("cURL Error (Gemini) #{$curl_error_num}: {$curl_error_msg}");
        return ['error' => "Network communication error with the AI service.", 'http_code' => $http_code];
    }

    if ($http_code >= 400) {
         error_log("Gemini API HTTP Error {$http_code}: {$response_json}");
         $responseData = json_decode($response_json, true);
         $errorMessage = $responseData['error']['message'] ?? 'An API error occurred with Gemini.';
         if ($http_code === 400 && isset($responseData['error']['details'][0]['reason']) && $responseData['error']['details'][0]['reason'] === 'API_KEY_INVALID') {
            $errorMessage = "Authentication error with AI service. Please check API key configuration.";
         }
        return ['error' => $errorMessage, 'http_code' => $http_code];
    }

    $response_data = json_decode($response_json, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error decoding Gemini JSON response: " . json_last_error_msg() . " | Response: " . $response_json);
        return ['error' => "Invalid response format from AI service.", 'http_code' => $http_code];
    }
    
    return $response_data;
}


function get_bot_response_with_gemini_and_db($user_message, $pdo, $apiKey, $base_url) {
    $user_message_lower = strtolower(trim($user_message));
    $bot_reply = "Processing your request with Gemini...";

    $db_context = "";

    if (strpos($user_message_lower, 'courses') !== false || strpos($user_message_lower, 'list courses') !== false) {
        try {
            $stmt = $pdo->query("SELECT title, category FROM Courses ORDER BY title LIMIT 3");
            $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if ($courses) {
                $db_context .= "Available courses at Crown Institute include: ";
                foreach ($courses as $course) {
                    $db_context .= htmlspecialchars($course['title']) . " (Category: " . htmlspecialchars($course['category']) . "), ";
                }
                $db_context = rtrim($db_context, ", ") . ". You can see all courses at " . $base_url . "courses.php. ";
            } else { $db_context .= "No courses seem to be listed right now. "; }
        } catch (PDOException $e) {
            error_log("DB error (Gemini chatbot courses): " . $e->getMessage());
            $db_context .= "I had trouble fetching course information. ";
        }
    } elseif (preg_match('/(?:info on|about|details for) course "([^"]+)"/i', $user_message_lower, $matches)) {
        $course_title_query = trim($matches[1]);
        try {
            $stmt = $pdo->prepare("SELECT title, description, category FROM Courses WHERE title LIKE ? LIMIT 1");
            $stmt->execute(["%$course_title_query%"]);
            $course = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($course) {
                $db_context .= "Regarding the course '" . htmlspecialchars($course['title']) . "': It's in the " . htmlspecialchars($course['category']) . " category. Description: " . substr(htmlspecialchars($course['description']), 0, 120) . "... ";
            } else { $db_context .= "I couldn't find course information for '$course_title_query'. "; }
        } catch (PDOException $e) {
            error_log("DB error (Gemini chatbot specific course): " . $e->getMessage());
            $db_context .= "I had trouble fetching details for that specific course. ";
        }
    } elseif (preg_match('/(?:materials for|what materials in) "([^"]+)"/i', $user_message_lower, $matches)) {
        $course_title_query = trim($matches[1]);
         try {
            $stmt_cid = $pdo->prepare("SELECT course_id FROM Courses WHERE title LIKE ? LIMIT 1");
            $stmt_cid->execute(["%$course_title_query%"]);
            $c_res = $stmt_cid->fetch(PDO::FETCH_ASSOC);
            if ($c_res) {
                $stmt_mats = $pdo->prepare("SELECT title, file_name FROM CourseMaterials WHERE course_id = ? ORDER BY upload_date DESC LIMIT 2");
                $stmt_mats->execute([$c_res['course_id']]);
                $mats = $stmt_mats->fetchAll(PDO::FETCH_ASSOC);
                if($mats){
                    $db_context .= "For the course '$course_title_query', some materials include: ";
                    foreach($mats as $m){ $db_context .= htmlspecialchars($m['title'] ?: $m['file_name']) . ", "; }
                    $db_context = rtrim($db_context, ", ") . ". These can be found on the course's unit page after enrollment. ";
                } else { $db_context .= "No specific materials are listed for '$course_title_query' at the moment. "; }
            } else { $db_context .= "I couldn't identify the course '$course_title_query' to check for materials. "; }
        } catch (PDOException $e) {
            error_log("DB error (Gemini chatbot materials): " . $e->getMessage());
            $db_context .= "I had trouble when I tried to look up course materials. ";
        }
    }
    
    $full_prompt = "You are a friendly and helpful learning assistant for a platform called 'Crown Institute'. ";
    if (!empty($db_context)) {
        $full_prompt .= "Consider the following information from our database: " . $db_context . " ";
    }
    $full_prompt .= "Now, please answer the user's question: " . $user_message;
    $full_prompt .= " Keep your response concise and suitable for a chatbot.";


    $gemini_response_data = call_gemini_api_with_curl($apiKey, $full_prompt);

    if (isset($gemini_response_data['error'])) {
        $bot_reply = "Sorry, I'm facing a technical difficulty: " . htmlspecialchars($gemini_response_data['error']);
        if ($gemini_response_data['http_code'] === 429) {
            $bot_reply = $gemini_response_data['error'] . " Please try again later.";
        }
    } elseif (isset($gemini_response_data['candidates'][0]['content']['parts'][0]['text'])) {
        $bot_reply = trim($gemini_response_data['candidates'][0]['content']['parts'][0]['text']);
    } else {
        $bot_reply = "I received an unusual response from the AI. Please try rephrasing your question.";
        error_log("Gemini response format unexpected (cURL): " . json_encode($gemini_response_data));
    }

    return $bot_reply;
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json_payload = file_get_contents('php://input');
    $data = json_decode($json_payload, true);
    $user_query = $data['message'] ?? '';

    if (!empty($user_query)) {
        $bot_response_text = get_bot_response_with_gemini_and_db($user_query, $pdo, $apiKey, $base_url);
        echo json_encode(['reply' => $bot_response_text]);
    } else {
        echo json_encode(['reply' => 'Please type a message.']);
    }
    exit();
} else {
    echo json_encode(['reply' => 'Invalid request method.']);
    exit();
}
?>