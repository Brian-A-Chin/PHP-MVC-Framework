<?php session_start();
    if(!isset($_SESSION['LoginAttempts'])){
        session_destroy();
        $_SESSION['LoginAttempts'] = 0;
    }

    require_once('../Configuration/GlobalConfig.php');
    require_once('../Libraries/Autoloader.php');
    require_once(ABSPATH . '/Libraries/3rdParty/sendgrid/vendor/autoload.php');
    require_once(ABSPATH . '/Libraries/3rdParty/Twilio/autoload.php');
    $libraries = new Autoloader(ABSPATH,'Libraries');
    $Bundles = new Autoloader(ABSPATH,'Bundles');
    $Configurations = new Autoloader(ABSPATH,'Configuration');

    $auth = new Authentication();
    $Twig = new Render(ABSPATH.'/Templates/SiteComponents');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?= $Twig->GetTemplate('MetaHead.twig',['BusinessName' => BusinessName,'Page_Title' => 'Login'])?>
    <?= StylesheetBundles::GetStyles(['Main','Modals','Login']); ?>
</head>
<body>
<?= $Twig->GetTemplate('Modal.twig')?>

<div class="loader"></div>
<form id="AuthorizationForm" class="aJaxForm" method="post">
    <div id="template_inject_container">
        <?php
            $Twig = new Render(ABSPATH.'/Views/Authentication');
            if(isset($_GET['resetcode'])){
                if($auth->VerifyPasswordResetLink([
                    'Code' => $_GET['resetcode']
                ])){
                    echo $Twig->GetTemplate('ResetPassword.twig',['reset_code'=>$_GET['resetcode'],'niceError'=>'Link Verified']);
                }else{
                    echo $Twig->GetTemplate('Login.twig',['niceError'=>'Link is no longer valid']);
                }
            }else if(isset($_GET['registercode'])){
                if($auth->VerifyRegisterCode([
                    'Code' => $_GET['registercode']
                ])){
                    echo $Twig->GetTemplate('Register.twig',['niceError'=>'Registration code verified','registration_code' => $_GET['registercode']]);
                }else {
                    echo $Twig->GetTemplate('Login.twig',['niceError'=>'Registration code is no longer valid']);
                }
            }else{
                echo $Twig->GetTemplate('Login.twig');
            }
        ?>
    </div>

</form>
<?php JavaScriptBundles::GetScripts(['Main','Authentication']); ?>
</body>
</html>




