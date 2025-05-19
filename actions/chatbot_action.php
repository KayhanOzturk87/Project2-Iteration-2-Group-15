<?php
session_start(); 
header('Content-Type: application/json');

// this function just reply basic message if user say something like hello or quiz
function get_simple_bot_response($user_message) {
    $user_message_lower = strtolower(trim($user_message));
    $bot_reply = "I'm sorry, I can only respond to a few basic queries right now. Try asking about 'hello', 'help', 'quiz', or 'course'."; // Default response

    // if user say hello or hi, then say hello back
    if (strpos($user_message_lower, 'hello') !== false || strpos($user_message_lower, 'hi') !== false) {
        $bot_reply = "Hello there! How can I assist you with your studies today?";
    } elseif (strpos($user_message_lower, 'help') !== false) {
        $bot_reply = "I can provide some basic information. You can ask about courses or quizzes. What do you need help with?";
    } elseif (strpos($user_message_lower, 'quiz') !== false) {
        $bot_reply = "Quizzes are usually found within each course after you enroll. You can access them from the unit page or your student dashboard if you're enrolled.";
    } elseif (strpos($user_message_lower, 'course') !== false || strpos($user_message_lower, 'subject') !== false) {
        $bot_reply = "You can browse all available courses from the 'All Courses' link in the navigation if you are a student. Teachers manage courses from their dashboard.";
    } elseif (strpos($user_message_lower, 'thank') !== false) {
        $bot_reply = "You're welcome! Let me know if there's anything else basic I can help with.";
    } elseif (strpos($user_message_lower, 'bye') !== false) {
        $bot_reply = "Goodbye! Happy studying!";
    }

    return $bot_reply;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $json_payload = file_get_contents('php://input');
    $data = json_decode($json_payload, true);

    $user_query = '';
    if (isset($data['message'])) {
        $user_query = $data['message']; // take the message user typed
    }

    // if user wrote something, send to bot and get reply
    if (!empty($user_query)) {
        $bot_response_text = get_simple_bot_response($user_query);
        echo json_encode(['reply' => $bot_response_text]);
    } else {
         // when user send nothing
        echo json_encode(['reply' => 'Please type a message.']);
    }
    exit();
} else {
    // if user try GET request, not allowed
    echo json_encode(['reply' => 'Invalid request method.']);
    exit();
}