<!DOCTYPE>
<html lang="pt-br">
    <head>
        <title>404</title>
        <style type="text/css">
            body{
                font-family: sans-serif, verdana,arial;
                color:#666;
            }
            h1,h3,p{
                margin: 0px;
            }
            a{
                text-decoration: none;
                color:#369;
                font-size: 14px;
                font-weight: bold;
                font-family: sans-serif, verdana,arial;
            }
            a:hover{
                text-decoration: underline;
            }
            .imgerr{
                position: absolute;
                bottom:0px;
                left: 32%;
            }
            .f404{
                font-size: 45px;
            }
            .notf{
                font-size: 9px;
                color:#999;
            }

        </style>
    </head>
    <body>
        <?php
        $HOME = explode( "404.php", $_SERVER['PHP_SELF'] );
        $HOME = $HOME[0];
        ?>
        <title>Ooops</title>
        <br /><br />
    <center>
        <h1>Ooops! <span class="f404">404</span></h1>
        <p class="notf">página não encontrada</p>
        <br />
        <h3>Eu poderia jurar que tivesse algo aqui, mas não tem!</h3>
        <br />
        <a href="<?= $HOME ?>">Volte para home, vamos começar de novo.</a>
        <img src="app/assets/images/default/404.jpg" class="imgerr"/>
    </center>
</body>
</html>
