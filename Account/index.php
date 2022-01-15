<?php
    session_start();
    require_once('../Configuration/GlobalConfig.php');
    require_once('../Libraries/Autoloader.php');
    require_once(ABSPATH . '/Libraries/3rdParty/sendgrid/vendor/autoload.php');
    require_once(ABSPATH . '/Libraries/3rdParty/Twilio/autoload.php');
    $libraries = new Autoloader(ABSPATH,'Libraries');
    $Bundles = new Autoloader(ABSPATH,'Bundles');
    $Configurations = new Autoloader(ABSPATH,'Configuration');
    Authentication::RequireAuthentication();
    $Account = new Account( Authentication::GetAccountID() );
    $CurrentController = Utilities::GetCurrentController();
    $CurrentMethod = Utilities::GetCurrentMethod();
    $CurrentURL = Utilities::GetCurrentUrl();
    if($CurrentController!='SetupController')
        $Account->RequireFullAccountSetup();

    $router = new Router(
        $CurrentController,
        $CurrentMethod
    );
    if(!empty($_POST)):
        $router->Route();
    else:
?>
<!DOCTYPE html>
    <html lang="en">
    <head>
        <?php
            $Twig = new Render(ABSPATH.'/Templates/SiteComponents');
            echo $Twig->GetTemplate('MetaHead.twig',
                [
                    'BusinessName' => BusinessName,
                    'Page_Title' => $CurrentMethod
                ]
            );
            StylesheetBundles::GetStyles(['Main','Account','Modals','Grids','Pagination','Admin']);
            StylesheetBundles::GetSpecificStyles($CurrentMethod);
        ?>
    </head>
    <body class="hold-transition sidebar-mini layout-fixed layout-navbar-fixed layout-footer-fixed">
        <div class="wrapper">
            <!-- Modals & Popups -->
            <?= $Twig->GetTemplate('Modal.twig')?>
            <!-- END Modals & Popups -->

            <!-- Top Navbar -->
            <?= $Twig->GetTemplate('Navbar.twig')?>
            <!-- END Top Navbar -->

            <!-- Main sidebar -->
            <?= $Twig->GetTemplate('Sidebar.twig',['CurrentUserPermissions' => $Account->GetPermissions(),'Identity' => $Account->GetIdentity()])?>
            <!-- END Main sidebar -->

            <!-- Template Render-->
            <?php $router->Route(); ?>
            <!-- End Template Render-->

        </div>
        <!-- Scripts -->

        <?=JavaScriptBundles::GetScripts(['Main','Pagination','Account']);?>
        <script type="text/javascript">const Core = new Modals('/Account/<?= count($CurrentURL) > 0 ? Utilities::GetCurrentUrl()[0] : 'dashboard';?>/');</script>
        <?=JavaScriptBundles::GetSpecificScripts($CurrentController);?>
        <?=JavaScriptBundles::GetSpecificScripts($CurrentMethod);?>
        <!-- END Scripts -->
    </body>
</html>
<?php endif;?>
