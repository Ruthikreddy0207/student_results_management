<?php
use SimpleExcel\SimpleExcel;

if(isset($_POST['import'])){

if(move_uploaded_file($_FILES['excel_file']['tmp_name'],$_FILES['excel_file']['name'])){
    require_once('SimpleExcel/SimpleExcel.php'); 
    
    $excel = new SimpleExcel('csv');                  
    
    $excel->parser->loadFile($_FILES['excel_file']['name']);           
    
    $fo = $excel->parser->getField(); 

    $count = 1;
    $db = mysqli_connect('localhost:3306','root','','resultdb');

    while(count($fo)>$count){
        $id = $fo[$count][0];
        $username=$fo[$count][1];
        $password = $fo[$count][2];
        $role = $fo[$count][3];

        $query = "INSERT INTO users(id,username,password,role) VALUES ('$id','$username','$password','$role')";
        mysqli_query($db,$query);
        $count++;
    }

    echo
    "
    <script>
    alert('Successfully Imported');
    document.location.href='admin.php';
    </script>
    ";
}
}
?>
 
}
  
}
?>