<?php




// Only process POST requests.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get the form fields and remove whitespace.
    $name = htmlentities(trim($_POST["name"]));
    $name = str_replace(array("\r","\n"),array(" "," "),$name);
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = htmlentities(trim($_POST["message"]));

    if(isset($_POST['g-recaptcha-response'])) {
        $captcha = $_POST['g-recaptcha-response'];
    } else {
        http_response_code(400);
        echo "Please check the reCaptcha.";
        exit;
    }

    $secretKey = "6LdOrRsTAAAAAA6zaSfSPiIGiXrzkakuEoG8DOHq";
    $verifyUrl = "https://www.google.com/recaptcha/api/siteverify";

    $response=file_get_contents($verifyUrl."?secret=".$secretKey."&response=".$captcha);

    $responseKeys = json_decode($response,true);

    if(intval($responseKeys["success"]) !== 1) {
        echo "Spammer! Get out!";
        exit;
    }

    // Check that data was sent to the mailer.
    if ( empty($name) OR empty($message) OR $responseKeys === null OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Set a 400 (bad request) response code and exit.
        http_response_code(400);
        echo "No can do buddy. Go try again!";
        exit;
    }

    // Set the recipient email address.
    $recipient = "codemasterkarol@gmail.com";

    // Set the email subject.
    $subject = "Cornerstone: New contact from $name";

    // Build the email content.
    $email_content = "Name: $name\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Message:\n$message\n";

    // Build the email headers.
    $email_headers = "From: $name <$email>";

    // Send the email.
    if (mail($recipient, $subject, $email_content, $email_headers)) {
        // Set a 200 (okay) response code.
        http_response_code(200);
        echo "Thank You! Your message has been sent and we will get back to you shortly!";
    } else {
        // Set a 500 (internal server error) response code.
        http_response_code(500);
        echo "Uh oh. Something went wrong!";
    }

} else {
    // Not a POST request, set a 403 (forbidden) response code.
    http_response_code(403);
    echo "THOU SHALT NOT EMAIL!";
}

?>
