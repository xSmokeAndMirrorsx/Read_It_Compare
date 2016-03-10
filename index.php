<?php
  session_start();
  function add_message($message){
    $dbhandle = new PDO("sqlite:chat.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $statement = $dbhandle->prepare("insert into messages ('username','message') values ('".$_SESSION["username"]."','".$message."')");
    $statement->execute();
  };
  
  function render_chat($filter=false){
    $dbhandle = new PDO("sqlite:chat.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    if ($filter != false){
      $statement = $dbhandle->prepare("select username, message from messages where message like '%".$filter."%' order by id DESC");
    } else {
      $statement = $dbhandle->prepare("select username, message from messages order by id DESC limit 0, 100");
    }
    $statement->execute();
    $messages = $statement->fetchAll(PDO::FETCH_ASSOC);
    $template = file_get_contents("chat.html");
    $message_template = file_get_contents("message.html");
    $message_rows = "";
    foreach($messages as $message){
      $message_rows .= str_replace("USERNAME", $message["username"], 
                            str_replace("MESSAGEHERE", $message["message"], $message_template));
    }
    echo str_replace("MESSAGESHERE", $message_rows, 
        str_replace("MYUSERNAME",$_SESSION["username"], $template));
  };
  
  function render_login($message = ""){
    $template = file_get_contents("login.html");
    echo str_replace("MESSAGEHERE", $message, $template);
  };

  function login($username, $pwd){
    $dbhandle = new PDO("sqlite:chat.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $statement = $dbhandle->prepare("Select * from users where username='".$username."' and password='".$pwd."'");
    $statement->execute();
    $results = $statement->fetch(PDO::FETCH_ASSOC);
    if (isset($results["username"])){
      $_SESSION["username"] = $results["username"];
      $_SESSION["logged_in"] = "1";
      render_chat();
    } else {
      render_login("Failed authentication");
    }
  };
  
  function logout(){
    session_start();
    unset($_SESSION["username"]);
    unset($_SESSION["logged_in"]);
  };
  
  function register($username, $pwd){
    $dbhandle = new PDO("sqlite:chat.db") or die("Failed to open DB");
    if (!$dbhandle) die ($error);
    $statement = $dbhandle->prepare("insert into users values ('".$username."','".$pwd."')");
    $statement->execute();
    $_SESSION["username"] = $username;
    $_SESSION["logged_in"] = "1";
  };
  if (isset($_SESSION["logged_in"])){
    if ($_SESSION["logged_in"] == "1"){
      if (isset($_POST["logout"])){
          logout();
          render_login();
      } else if (isset($_POST["message"])){
          add_message($_POST["message"]);
          render_chat();
      } else if (isset($_POST["filter"])){
          render_chat($_POST["filter"]);
      } else {
        render_chat();
      }
    }
  } else {
    if (isset($_POST["login"])){
        login($_POST["username"], $_POST["password"]);
    } else if (isset($_POST["register"])) {
        register($_POST["username"], $_POST["password"]);
        render_chat();
    } else {
        render_login();
    }
  }
?>