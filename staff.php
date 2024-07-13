<?php
use SimpleExcel\SimpleExcel;

if(isset($_POST['import'])){

if(move_uploaded_file($_FILES['excel_file']['tmp_name'],$_FILES['excel_file']['name'])){
    require_once('SimpleExcel/SimpleExcel.php'); 
    
    $excel = new SimpleExcel('csv');                  
    
    $excel->parser->loadFile($_FILES['excel_file']['name']);           
    
    $foo = $excel->parser->getField(); 

    $count = 1;
    $db = mysqli_connect('localhost:3306','root','','resultdb');

    while(count($foo)>$count){
        $student_id = $foo[$count][0];
        $subject= $foo[$count][1];
        $grade = $foo[$count][2];
        $student_name = $foo[$count][3];
        $semester = $foo[$count][4];

        $query = "INSERT INTO results(student_id,subject,grade,student_name,semester) VALUES ('$student_id','$subject','$grade','$student_name','$semester')";
        mysqli_query($db,$query);
        $count++;
    }

    echo
    "
    <script>
    alert('Successfully Imported');
    document.location.href='staff1.php';
    </script>
    ";
}
}
?>
 
}
  
}
?>