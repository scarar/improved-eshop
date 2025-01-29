<?php
session_start();
include('pgp-2fa.php');
//
//putenv("GNUPGHOME=/tmp");
//$pubkey = "-----BEGIN PGP PUBLIC KEY BLOCK-----
//Version: GnuPG v1.2.6 (GNU/Linux)
//
//mQGiBEe68W8RBACVuFuv4d+roDSCdRO1SuO8dQwds4VTjVOqgVKQtq6+8Fe95RY8
//BAf1IyLj4bxvWPhr0wZdVwTosD/sFoPtdCyhVcF932nP0GLHsTEeVwSz9mid22HI
//O4Kmwj2kE+I+C9QdzAg0zaWQnVaF9UC7pIdMR6tEnADI8nkVDdZ+zb2ziwCg6Yqu
//tk3KAzKRT1SNUzTE/n9y2PED/1tIWiXfGBGzseX0W/e1G+MjuolWOXv4BXeiFGmn
//8wnHsQ4Z4Tzk+ag0k+6pZZXjcL6Le486wpZ9MAe6LM31XDpQDVtyCL8t63nvQpB8
//TUimbseBZMb3TytCubNLGFe5FnNLGDciElcD09d2xC6Xv6zE2jj4GtBW1bXqYWtl
//jm0PA/4u6av6o6pIgLRfAawspr8kaeZ8+FU4NbIiS6xZmBUEQ/o7q95VKGgFVKBi
//ugDOlnbgSzBIwSlsRVT2ivu/XVWnhQaRCotSm3AzOc2XecqrJ6F1gqk0n+yP/1h1
//yeTvvfS5zgqNTG2UmovjVsKFzaDqmsYZ+sYfwc209z9PY+6FuLQnQXBhY2hlVGVz
//dCAoVGVzdGluZykgPGFwYWNoZUBsb2NhbGhvc3Q+iF4EExECAB4FAke68W8CGwMG
//CwkIBwMCAxUCAwMWAgECHgECF4AACgkQJE9COu2PFIEGDwCglArzAza13xjbdR04
//DQ1U9FWQhMYAnRrWQeGTRm+BYm6SghNpDOKcmMqruQENBEe68XAQBADPIO+JFe5t
//BQmI4l60bNMNSUqsL0TtIP8G6Bpd8q2xBOemHCLfGT9Y5DN6k0nneBQxajSfWBQ5
//ZdKFwV5ezICz9fnGisEf9LPSwctfUIcvumbcPPsrUOUZX7BuCHrcfy1nebS3myO/
//ScTKpW8Wz8AjpKTBG55DMkXSvnx+hS+PEwADBQP/dNnVlKYdNKA70B4QTEzfvF+E
//5lyiauyT41SQoheTMhrs/3RIqUy7WWn3B20aTutHWWYXdYV+E85/CarhUmLNZGA2
//tml1Mgl6F2myQ/+MiKi/aj9NVhcuz38OK/IAze7kNJJqK+UEWblB2Wfa31/9nNzv
//ewVHa1xHtUyVDaewAACISQQYEQIACQUCR7rxcAIbDAAKCRAkT0I67Y8UgRwEAKDT
//L6DwyEZGLTpAqy2OLUH7SFKm2ACgr3tnPuPFlBtHx0OqY4gGiNMJHXE=
//=jHPH
//-----END PGP PUBLIC KEY BLOCK-----";
//
//$enc = (null);
//$res = gnupg_init();
//echo "gnupg_init RTV = <br/><pre>\n";
//var_dump($res);
//echo "</pre>\n";
//$rtv = gnupg_import($res, $pubkey);
//echo "gnupg_import RTV = <br/><pre>\n";
//var_dump($rtv);
//echo "</pre>\n";
//$rtv = gnupg_addencryptkey($res, "A232D3244202BEB8F4161BD131E505F540285D09");
//echo "gnupg_addencryptkey RTV = <br /><pre>\n";
//var_dump($rtv);
//echo "</pre>\n";
//$enc = gnupg_encrypt($res, "just a test to see if anything works");
//echo "Encrypted Data: " . $enc . "<br/>";

$pgp = new pgp_2fa();
$msg = '';
if($_SERVER['REQUEST_METHOD'] == 'POST' and !isset($_POST['pgp-key'])){

    if($pgp->compare($_POST['user-input'])){
        $msg = '<div class="alert alert-success">Success!</div>';
    }else{
        $msg = '<div class="alert alert-danger">Fail!</div>';
    }


} else {
    $pgp->generateSecret();
    $pgpmessage = $pgp->encryptSecret($_POST['pgp-key']);
}
?>
<!DOCTYPE>
<html>
<head>
    <title>2FA-PGP</title>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootswatch/3.3.5/flatly/bootstrap.min.css">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
<h1 class="text-center">2FA-PGP</h1>
<?php echo $msg ?>
<div class="container">
    <label for="pgp-msg">Encrypted Code:</label>
    <textarea rows="15" class="form-control" name="pgp-msg" id="pgp-msg"><?php echo $pgpmessage ?></textarea>
    <form class="form" action="pgp.php" method="post">

        <label for="user-input">Decrypted Code:</label>
        <input type="text" name="user-input" id="user-input" class="form-control">
        <br/>
        <button class="btn btn-primary form-control">Check!</button>
    </form>
</div>
<h6 class="text-center">This awesome theme is called <a href="//bootswatch.com/flatly">'Flatly'</a> and was made by <a href="//bootswatch.com/">Bootswatch.com</a>!</h6>
</body>
</html>