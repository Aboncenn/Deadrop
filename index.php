<?php
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <script src="jquery.js"></script>
  <script src ="library.js"></script>
  <script src="highlight.pack.js"></script>
  <script>hljs.initHighlightingOnLoad();</script>
</head>
<style>
  body{
    background-color: #1fbed6;
    font-size: 20px;
  }
  p, h1, form{
    color : white;
  }
  form{
    height: 100px;
    padding-left: 25%;
  }
  input{
    margin: 2%;
  }
  button{
    margin-left: 12%;
    margin-top: 2%;
  }
</style>
<body>
  <h1>DEADROP</h1>
  <pre><code class="html">
    <div id="empty"></div>
  </code></pre>
<?php
// stock id unique
$id = uniqid('', true);
// Pour ne pas avoir d'erreur en js
$idunique= 0;

// Si le POST est vide ou si on ne recoit pas le texte
if ($_POST != null && !empty($_POST['crypto'])) {
    // recupération id
    $iduniq= $_POST['id'];

    // folder root
    $folder1 = substr($iduniq, 0, 2);

    // folder
    $folder2 = substr($iduniq, 3, 2);

    // files
    $files = substr($iduniq, 5, 24);

    //.htaccess
    $access = "Allow from none
Deny from all";

    //Création premier dossier + .htaccess
    mkdir("data/".$folder1, 0700);
    $folder_htaccess = fopen("data/".$folder1.'/.htaccess', "w") or die("Unable to open file!");
    fwrite($folder_htaccess, $access);
    fclose($folder_htaccess);

    //Création second dossier + .htaccess
    mkdir("data/".$folder1.'/'.$folder2, 0700);
    $folder_htaccess1 = fopen("data/".$folder1.'/'.$folder2.'/.htaccess', "w") or die("Unable to open file!");
    fwrite($folder_htaccess1, $access);
    fclose($folder_htaccess1);

    // Création d'un fichier du nom de l'id
    $write = fopen("data/".$folder1.'/'.$folder2.'/'.$files, "w") or die("Unable to open file!");
    fwrite($write, $_POST['time']);
    fwrite($write, $_POST['crypto']);
    fclose($write);

    // Lecture du fichier pour le burn
    $read =  file_get_contents("data/".$folder1.'/'.$folder2."/". $files);
    // Vérification du timestamp
    $check = json_decode($read);

    // Création du .bar
    if ($check->burining == "true") {
        $write = fopen("data/".$folder1.'/'.$folder2.'/'.$files.".bar", "w") or die("Unable to open file!");
        fclose($write);
    }

    // Si nous sommes dans un état de GET
} elseif ($_GET != null && !empty($_GET)) {
    // récupération de l'id
    $idunique= $_GET['id'];
    // folder root
    $folder1 = substr($idunique, 0, 2);

    // folder
    $folder2 = substr($idunique, 3, 2);

    // files
    $files = substr($idunique, 5, 24);

    // Si l'url est mauvaise, on s'évite la création des dossiers
    if (file_exists("data/".$folder1.'/'.$folder2."/". $files) == null) {
        echo "Ce n'est pas le bon url, pour corriger : https://youtu.be/dQw4w9WgXcQ";
    } else {
        // Lecture du fichier
        $read =  file_get_contents("data/".$folder1.'/'.$folder2."/". $files);
        // Vérification du timestamp
        $check = json_decode($read);
        //flag timer
        $timer = 0;
        // Vérification de la péremption
        if ($check->time == time() || $check->time < time() && $check->time != 0) {
            $timer = 1;
            unlink("data/".$folder1.'/'.$folder2."/". $files);
            if (file_exists("data/".$folder1.'/'.$folder2."/". $files . '.bar')!= false) {
                unlink("data/".$folder1.'/'.$folder2."/". $files. '.bar');
            }
            // Suppression des dossiers vides
            $fol2 = new FilesystemIterator("data/".$folder1.'/'.$folder2, FilesystemIterator::SKIP_DOTS);
            if (iterator_count($fol2) == 1) {
                unlink("data/".$folder1.'/'.$folder2."/.htaccess");
                rmdir("data/".$folder1.'/'.$folder2);
            }
            $fol1 = new FilesystemIterator($folder1, FilesystemIterator::SKIP_DOTS);
            if (iterator_count($fol1) == 1) {
                unlink("data/".$folder1."/.htaccess");
                rmdir("data/".$folder1);
            }
        }
        // suppression du fichier si burn after reading
        if (file_exists("data/".$folder1.'/'.$folder2."/". $files . '.bar') != false) {
            unlink("data/".$folder1.'/'.$folder2."/". $files);
            unlink("data/".$folder1.'/'.$folder2."/". $files. '.bar');
        }
    } ?>
<script>

  // Variable vérifiant si le fichier doit être afficher
  var timer = '<?php echo $timer; ?>';

  if(timer == 0){
    // On récupère l'id, l'url, le fichier, le morceau d'url entre le # et le =.
    var id = '<?php echo $idunique; ?>';
    var url = window.location.href;
    var data = '<?php echo $read; ?>';
    var message = url.substring(
            url.lastIndexOf("#") + 1,
            url.lastIndexOf("=") +1
    );
    var decrypt = sjcl.decrypt(message, data);
    var input = document.createElement("P");
    var title = document.createTextNode(decrypt);
    input.appendChild(title);
    document.getElementById("empty").appendChild(input);
  }else{
    alert("Votre fichier est trop vieux pour être lu !");
  }
  </script>
<?php
} else {
        ?>
  <form action='index.php' method='POST' id="post_submit">
    <input type="hidden" name='id' value='<?php echo $id; ?>' id="id_txt_post"/>
    <textarea name="crypto" id="crypto_txt" rows="12" cols= "50" placeholder="votre texte ici"></textarea>
    <br>
    <input type ="checkbox"  id="check_burn"/>burn after reading ?</input>
    <br>
    <label>Périme après 5 minutes / 1 heure / 1 semaine / jamais</label>
    <br>
    <select id="select">
     <option value="5m">5 minutes</option>
     <option value="1h">1 heure</option>
     <option value="1s">1 semaine</option>
     <option value="j" selected>jamais</option>
    </select>
    <br>
    <button class='btn btn-warning' type="submit"><h4>Let's encrypt !</h4></button></a>

  </form>
<?php
    };
?>
</body>
<script>
  // id unique
  var input = '<?php echo $id; ?>';
  // clé random
  var monRandom = sjcl.random.randomWords(8, 10);
  // Passage en base64...
  var key2 = sjcl.codec.base64.fromBits(monRandom);
  // Passage en replace de la penitence
  var key1 = key2.replace('+', '$');
  var key0 = key1.replace('/', '$');
  var key = key0;

  // Function POST
  $('#post_submit').submit(
    function (event) {

      // Récupération du message pour le chiffrer
      var message = document.getElementById("crypto_txt").value
      var burn = document.getElementById("check_burn").checked;
      var temps = document.getElementById("select").value;
      // if time
      if(temps == "5m"){
        var tamp =  Date.now() + (60 * 300);
      }else if(temps == "1h"){
        var tamp =  Date.now() + (60 * 60 * 60);
      }else if(temps == "1s"){
        var tamp = Date.now() + (7 * 24 * 60 * 60);
      }else if(temps == "j"){
        var tamp = 0;
      }

      // POUF POUF MAGIE
      var encrypt = sjcl.encrypt(key, message);
      event.preventDefault();
      // Ajout dans le JSON du time
      var data = JSON.parse(encrypt);  //parse the JSON
      var test = Object.assign({"time" : tamp},data);
      var burn = Object.assign({"burining" : burn},test);
      var txt = JSON.stringify(burn);
      // Renvoi en post le message chiffré et son id
      var post = $.ajax({
          type: "POST",
          url: "index.php",
          data: {
            crypto: txt,
            id: input,
          },
          success: function( element ) {
            // c'est l'echec
          },
          dataType: "json"
        });
      var post = $.post( "index.php", function( data ) {
            // Alerte indiquant l'url
        alert("Votre adresse est : http://localhost:8888/deadrop/index.php?id="+ input + "#"+key);
      });
  });
</script>
