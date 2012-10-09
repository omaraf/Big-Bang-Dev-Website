<?php

// CONFIGURATION --------------------------------------------------------------

// This is the email where the contact mails will be sent to.
$config['recipient'] = 'soporte@bigbangdev.com';

// This is the subject line for contact emails.
// The variable %name% will be replaced with the name of the sender.
$config['subject'] = 'Mensaje de Contacto de sitio web de BigBangDev de %name%';

// These are the messages displayed in case of form errors.
$config['errors'] = array
(
	'no_name'       => 'Favor de introducir un nombre.',
	'no_email'      => 'Es necesario que introduzca su correo electrónico.',
	'invalid_email' => 'La dirección de correo que introdujo es inválida.',
	'no_message'    => 'Por favor incluya un mensaje.',
	'no_capth'    => 'El resultado de la suma es inválido.',
);

// END OF CONFIGURATION -------------------------------------------------------


// Ignore non-POST requests
if ( ! $_POST)
	exit('Nothing to see here. Please go back to the site.');

// Was this an AJAX request or not?
$ajax = (isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

// Set the correct HTTP headers
header('Content-Type: text/'.($ajax ? 'plain' : 'html').'; charset=utf-8');

// Extract and trim contactform values
$name    = isset($_POST['name']) ? trim($_POST['name']) : '';
$email   = isset($_POST['email']) ? trim($_POST['email']) : '';
$telephone   = isset($_POST['telephone']) ? trim($_POST['telephone']) : '';
$city   = isset($_POST['city']) ? trim($_POST['city']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';
$capth = isset($_POST['capth']) ? trim($_POST['capth']) : '';

// Take care of magic quotes if needed (you really should have them disabled)
set_magic_quotes_runtime(0);
if (get_magic_quotes_gpc())
{
	$name    = stripslashes($name);
	$email   = stripslashes($email);
	$message = stripslashes($message);
	$city = stripslashes($city);
	$telephone = stripslashes($telephone);
	$capth = stripslashes($capth);
}

// Initialize the errors array which will also be sent back as a JSON object
$errors = NULL;

// Validate name
if ($name == '' || strpos($name, "\r") || strpos($name, "\n"))
{
	$errors['name'] = $config['errors']['no_name'];
}

// Validate email
if ($email == '')
{
	$errors['email'] = $config['errors']['no_email'];
	$errors['err'] = true;
}
elseif ( ! preg_match('/^[-_a-z0-9\'+*$^&%=~!?{}]++(?:\.[-_a-z0-9\'+*$^&%=~!?{}]+)*+@(?:(?![-.])[-a-z0-9.]+(?<![-.])\.[a-z]{2,6}|\d{1,3}(?:\.\d{1,3}){3})(?::\d++)?$/iD', $email))
{
	$errors['email'] = $config['errors']['invalid_email'];
	$errors['err'] = true;
}

// Validate message
if ($message == '')
{
	$errors['message'] = $config['errors']['no_message'];
	$errors['err'] = true;
}else {
	$message = 'Telefono: '. $telephone . "\r\n" . 'Ciudad: '. $city . "\r\n" . 'Mensaje: ' . $message;
}

if($capth != 4){
	$errors['no_capth'] = $config['errors']['no_capth'];
	$errors['err'] = true;
}

// Validation succeeded
if (empty($errors))
{
	// Prepare subject line
	$subject = str_replace('%name%', $name, $config['subject']);

	// Additional mail headers
	$headers  = 'Content-Type: text/plain; charset=utf-8'."";
	$headers .= 'From: '.$email;

	// Send the mail
	if ( ! mail($config['recipient'], $subject . ' ('. $email.')', $message, $headers))
	{
		$errors['server'] = 'Al parecer existe un problema con el servidor. '.
		                    'Puede usted enviar su mensaje directamente al correo  '.$config['recipient'].'? Muchas gracias.';
		$errors['err'] = 'True';
	}
	else{
		$errors['code'] = '200';
	}
}

if ($ajax)
{
	// Output the possible errors as a JSON object
	echo array_to_json($errors);
}

/*else
{
	// Show a simple HTML feedback message in case of non-javascript support
	if (empty($errors))
	{
		echo '<h1>Gracias</h1>';
		echo '<p>Su mensaje ha sido enviado. Pronto nos pondremos en contacto con usted.</p>';
	}
	else
	{
		echo '<h1>Oops!</h1>';
		echo '<p>Please go back and fix the following errors:</p>';
		echo '<ul><li>';
		echo implode('</li><li>', $errors);
		echo '</li></ul>';
	}
}*/

function array_to_json( $array ){

    if( !is_array( $array ) ){
        return false;
    }

    $associative = count( array_diff( array_keys($array), array_keys( array_keys( $array )) ));
    if( $associative ){

        $construct = array();
        foreach( $array as $key => $value ){

            // We first copy each key/value pair into a staging array,
            // formatting each key and value properly as we go.

            // Format the key:
            if( is_numeric($key) ){
                $key = "key_$key";
            }
            $key = '"'.addslashes($key).'"';

            // Format the value:
            if( is_array( $value )){
                $value = array_to_json( $value );
            } else if( !is_numeric( $value ) || is_string( $value ) ){
                $value = '"'.addslashes($value).'"';
            }

            // Add to staging array:
            $construct[] = "$key: $value";
        }

        // Then we collapse the staging array into the JSON form:
        $result = "{ " . implode( ", ", $construct ) . " }";

    } else { // If the array is a vector (not associative):

        $construct = array();
        foreach( $array as $value ){

            // Format the value:
            if( is_array( $value )){
                $value = array_to_json( $value );
            } else if( !is_numeric( $value ) || is_string( $value ) ){
                $value = '"'.addslashes($value).'"';
            }

            // Add to staging array:
            $construct[] = $value;
        }

        // Then we collapse the staging array into the JSON form:
        $result = "[ " . implode( ", ", $construct ) . " ]";
    }

    return $result;
}

?>