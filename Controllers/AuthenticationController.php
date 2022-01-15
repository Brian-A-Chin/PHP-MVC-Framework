<?php
if (empty($_POST)){header("location:/404"); exit;}
session_start();
require_once('../Configuration/GlobalConfig.php');
require_once('../Libraries/Autoloader.php');
require_once(ABSPATH . '/Libraries/3rdParty/sendgrid/vendor/autoload.php');
require_once(ABSPATH . '/Libraries/3rdParty/Twilio/autoload.php');
$libraries = new Autoloader(ABSPATH,'Libraries');
$Bundles = new Autoloader(ABSPATH,'Bundles');
$Configurations = new Autoloader(ABSPATH,'Configuration');
$REQUEST = explode('/',$_SERVER['HTTP_REFERER']);

//Logout
if(isset($_POST['Logout'])){
    session_destroy();
}


if($REQUEST[3] === 'account'):
    $auth = new Authentication();

    //Verify Login
    if(isset($_POST['VL'])){
        echo json_encode($auth->GetCurrentLoginStatus());
        return false;
    }

    //Login
    if(isset($_POST['Login'])){
        $uniqueValue = isset($_POST['Login']) ? $_POST['Login'] : false;
        $password = isset($_POST['Password']) ? $_POST['Password'] : false;
        $remember = isset($_POST['Remember']) ? $_POST['Remember'] : false;
        echo json_encode($auth->Login([
            'UniqueValue' => $uniqueValue,
            'Password' => $password,
            'Remember' => $remember,
        ]));
        return false;
    }

    //Get template
    if(isset($_POST['GetTemplate'])) {
        $Twig = new Render(ABSPATH.'/Views/Authentication');
        echo $Twig->GetTemplate($_POST['GetTemplate']);
        return false;
    }

    //Register
    if(isset($_POST['RegistrationEmail'])){
        $identity = $_POST['Identity'] ?? false;
        $email = $_POST['RegistrationEmail'] ?? false;
        $phone = $_POST['RegistrationPhone'] ?? false;
        $password = $_POST['Password'] ?? false;
        $code = $_POST['RegistrationCode'] ?? false;
        echo json_encode($auth->Register([
            'Identity' => $identity,
            'Email' => $email,
            'Phone' => $phone,
            'Password' => $password,
            'Code' => $code
        ]));
        /*
        require_once('../Libraries/3rdParty/RandomNames/randomNameGenerator.php');
        $randomName = new randomNameGenerator();
        $options = ['4','3','2'];
        for($x = 0; $x < 1000; $x++) {
            $name = $randomName->getName();
            $auth->Register([
                'Identity' => $name,
                'Email' => str_replace(' ', '_', $name).'@example.com',
                'Password' => 'password$',
                'Code' => $code
            ]);
        }
        */

        return false;
    }

    //new location login
    if(isset($_POST['two_factor_code'])){
        echo json_encode($auth->VerifySecurityCode($_POST['two_factor_code']));
        return false;
    }

    if(isset($_POST['ResendCode'])){
        echo json_encode($auth->SendVerificationCode([
            'AccountID' => BaseClass::Crypto($_POST['SignatureOne'],'Decrypt'),
            'Email' => $_POST['SignatureTwo'],
            'Resent' => true
        ]));
        return false;
    }

    //forgot_password
    if(isset($_POST['forgot_password'])){
        echo json_encode($auth->SendAccountPasswordResetLink([
            'Email' => $_POST['forgot_password']
        ]));
        return false;
    }

    if(isset($_POST['Resetcode'])){
        echo json_encode($auth->ResetPasswordByResetCode([
            'Password' => $_POST['Password'],
            'Code' => $_POST['Resetcode'],
        ]));
        return false;
    }

endif;


