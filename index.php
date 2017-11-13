<?php
// Connect to DB
$mysql_name = "first";
$mysql_host = "localhost";
$mysql_user = "root";
$mysql_password = "";
$mysql_table = "first_table";

$link = mysqli_connect($mysql_host, $mysql_user, $mysql_password, $mysql_name);
if (!$link) {
    echo "There is no connection." . PHP_EOL;
    echo "Error code: " . mysqli_connect_errno() . PHP_EOL;
    echo "Error text: " . mysqli_connect_error() . PHP_EOL;
    exit;
}
$query = "CREATE TABLE IF NOT EXISTS `$mysql_table`" . "(
        link_id  INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
        link_hash  VARCHAR(100),
        link_short_url TEXT,
        link_long_url TEXT
    )";
mysqli_query($link, $query);
if (!mysqli_query($link, $query)) {
    echo 'Can\'t create database' . mysqli_error($link);
    exit;
}

// View of main page
function view_page($content = '')
{
    echo '<!DOCTYPE HTML> <html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/css/bootstrap.min.css" integrity="sha384-PsH8R72JQ3SOdhVi3uxftmaW6Vc51MKb0q5P2rRUpPvrszuE4W1povHYgTpBfshb" crossorigin="anonymous">
        <title>CUT_THE_LINK</title>
    </head>
    <body>
    <nav class="navbar navbar-expand-lg bg-primary">
  <h1 class="navbar-brand mb-0" style="color: white">CUT_THE_LINK</h1>
  <span class="font-weight-light" style="color: white">Easiest way to make your link shorter</span>
  <ul class="navbar-nav flex-row ml-md-auto">
    <li class="nav-item">
  
        <a href="http://vitaliykapitonov.esy.es/index.php" class="btn btn-success  nav-item nav-link mr-md-2" style="color: white;border-color: whitesmoke">Kapitonov V.</a>

   </li>
   
   
  </ul>
  
  </nav>
        <div class="container">
       <br>
            <div class="row">
                    <div class="col-lg-4">
                    <form  action="/" method="post" >
                        <input type="hidden" name="do" value="add">
                        <div class="form-group">
                            <label for="id1">Enter your long link.</label>
                            <input type="text" id="id1" class="form-control col-lg-12" name="url" value="" placeholder="http://example.com"> 
                        </div>
                        <div class="form-group">
                            <button type="submit"  class="btn btn-primary col-lg-12">Press to make link shorter</button> 
                        </div>
                        <div class="form-group">';
    echo $content;
    echo '</div> </form>  </div> </div> </body> </html>';
}

//Redirect
if (isset($_GET['to'])) {
    $rr = $_GET['to'];
    $rr = htmlspecialchars($rr, ENT_QUOTES);
    $link_short_url = trim($rr);
    if ($link_short_url) {
        $query = "SELECT * FROM `$mysql_table` WHERE `link_short_url`='$link_short_url'";
        $sql_result = mysqli_query($link, $query);
        $row = mysqli_fetch_assoc($sql_result);
        if (isset($row['link_long_url'])) {
            Header('Status: 301 Moved Permanently');
            Header('Location: ' . $row['link_long_url']);

        } else {

            $content = '<div class="alert alert-danger" role="alert">There is no short link for this link in database!(</div>';
            view_page($content);
        }
    } else {
        view_page($content);
    }
} //если добавляем по ссылке

elseif (isset($_POST['do']) && $_POST['do'] == 'add') {

    if (isset($_POST['url'])) {
        $link_long_url = trim($_POST['url']);
        $link_long_url = htmlspecialchars($link_long_url, ENT_QUOTES);//link_long_url

        $link_hash = CRC32($link_long_url);
        if ($link_hash) {

            $query = "SELECT * FROM  `$mysql_table` WHERE `link_hash`='$link_hash' LIMIT 1";
            $sql_result = mysqli_query($link, $query);
            $row = mysqli_fetch_assoc($sql_result);

            if (isset($row['link_hash'])) {
                $link_short_url = $row['link_short_url'];
            } else {


                $parsed_long_url = parse_url($link_long_url);

                if (isset($parsed_long_url{"host"})) {
                    $link_short_url = $parsed_long_url{"host"};//link_short_url


                    $query = "INSERT INTO `$mysql_table` (`link_hash`,`link_long_url`)" .
                        " VALUES ('$link_hash','$link_long_url')";
                    $res = mysqli_query($link, $query);
                    if (!$res) {
                        echo 'Insert error: ' . mysqli_error($link);
                        exit;
                    }
                    $link_short_url = "$link_short_url" . ".$link_hash";


                    $query = "UPDATE `$mysql_table` SET `link_short_url`='$link_short_url' WHERE `link_hash`='$link_hash'   LIMIT 1";
                    $res = mysqli_query($link, $query);
                    if (!$res) {
                        echo 'Update error: ' . mysqli_error($link);
                        exit;
                    }

                } else {
                    $content = '<div class="alert alert-danger" role="alert">This is not link!(</div>';
                    view_page($content);
                    exit;
                }


            }


            $content = '<input type="text"  class="alert alert-success col-lg-12"  value="http://' . getenv('HTTP_HOST') . '?' . 'to=' . $link_short_url . '" onclick="this.select();">';
            view_page($content);
        } else {

            Header('Status: 301 Moved Permanently');
            Header('Location: /');
            exit;
        }
    } else {

        Header('Status: 301 Moved Permanently');
        Header('Location: /');
        exit;
    }
} else {
    view_page();
}

?>
