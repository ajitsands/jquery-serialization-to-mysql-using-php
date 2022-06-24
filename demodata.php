// Step 1 HTML form 

<form id="member_registraion">
    
    
    <input name="names" value="Ajit" />
    <input name="dob" value="1973-05-25" />
    <input name="nationality^state" value="Indian^kerala" />
    <input name="gender" value="Male" />
  
</form>
 <button id="click_me">Post</button>
 
 // Step 2 Jquery in Html File 
 
 $("#click_me").click(function(){
         var data = $('#member_registraion').serializeArray().reduce(function(obj, item) {
              obj[item.name] = item.value;
           return obj;
          }, {});

         $.post("createsql.php",{datas:data , t_name : "member_details" },function(res){ // t_name = table name you want to insert
            console.log(res);
        });
     
});

// Step 4  MySQL Table Structure 

CREATE TABLE `members` (
  `ids` int(11) NOT NULL,
  `names` varchar(500) NOT NULL,
  `dob` date NOT NULL,
  `nationality` varchar(100) NOT NULL,
  `gender` varchar(10) NOT NULL,
  `entered_by_id` int(11) DEFAULT NULL,
  `entered_by_name` varchar(200) DEFAULT NULL,
  `entered_by_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=MyISAM DEFAULT CHARSET=latin1;



// Step 3 Create your Php page - hear in Example createsql.php

<?PHP


    $formdata = $_POST['datas'];
    $table_name = $_POST['t_name'];
   
   Option 1) CreateSQLQueryForInsert($formdata,$table_name); // this will generate the Insert SQL Statement
    
   // If you want to add other column name and column values appart from the serialized data from the form  
   // here example if you want to add entered userid and entered name of the current user to sqlstring 
   // here in mysql entered userid = entered_by_id and entered name = entered_by_name these arre the column names. You have to use same column name in the table here.
   // array('1','Ajit Kumar') these values may get from Sessions or Cookies 
   
   Option 2) CreateSQLQueryForInsert($formdata,$table_name,array('entered_by_id','entered_by_name'), array('1','Ajit Kumar'));
    // Column Name and  Should be Array Parameter Eg : array('Fist Value','Second Value') as So on
    // Last 2 Parameters are Optional 
  
  
  function CreateSQLQueryForInsert($form_data,$table_name,$colNames=array(),$column_value=array()) 
   {
       // Creating the Insert Column Names 
        $insertSQL = '"insert into '.$table_name.'(';
        foreach($form_data as $key => $value) 
        {
            //$key = explode('-',$key);
            $param = $param.$key.",";
        }
        // Adding Specified column name as parameter pass as colNames this columns adding to the insert column
        foreach($colNames as $column){
             $param = $param.$column.",";
        }
        // For Removing the last coma from the string 
        $cols =  substr_replace($param ,") values (",-1)  ;
        
        
        // Adding Values to the inser Query to the above String 
        foreach($form_data as $key => $value) 
        {
            $values = $values."'".$value."',";
        }
        // adding additional Values If any 
        foreach($column_value as $columnvalue){
             $values = $values."'".$columnvalue."',";
        }        
                
        // Removing last Coma from the string 
        $vals =  substr_replace($values ,')"',-1)  ;
        
        // Adding Column names and Values to gather to mahe the Redy Executable SQL String 
        $final_SQL = $insertSQL.$cols.$vals;
        
        return $final_SQL;
       
   
   }  
   
   
   // The above function Will return a SQL String for Insert Command 
   
  //  the Above Option 1) Result Below String 
  
  $SQLString =   CreateSQLQueryForInsert($formdata,$table_name) 
  
  // Now $SQLString have this value - > "insert into member_details(names,dob,nationality^state,gender) values ('Ajit','1973-05-25','Indian^kerala','Male')"
   
  //  the Above Option 2) Result Below String
  
   $SQLString =   CreateSQLQueryForInsert($formdata,$table_name,array('entered_by_id','entered_by_name'), array('1','Ajit Kumar'));
   
 // Now $SQLString have this value - >  "insert into member_details(names,dob,nationality^state,gender,entered_by_id,entered_by_name) values ('Ajit','1973-05-25','Indian^kerala','Male','1','Ajit Kumar')"
  
  
  // Step 4 - Now your String Has been Generated it is ready to Execute in MYSql .  
  // you have to create a prosedure in My Sql With Prepare statement 
  // Eg Below
  
DELIMITER $$
CREATE  PROCEDURE `insert_data`(IN `sql_query` TEXT, OUT `ret_member_id` VARCHAR(20))
    NO SQL
BEGIN

				SET @p_SQLQUERY=sql_query;
                PREPARE s FROM @p_SQLQUERY;
                EXECUTE s;
                DEALLOCATE PREPARE s;


select last_insert_id() into @ret_member_id ;
SET ret_member_id = @ret_member_id;

END$$
DELIMITER ;
  
// Step 5 - Now you have to call the above prosedure in PHP file   
  
  public function ExecuteProcedure($SQL)
	{
  // You have to create the connection here to conn object
			$retval = mysqli_query(conn, $SQL);
			if (!($res = conn->query("SELECT @msg as _p_out"))) {
				echo "Fetch failed: (" . conn->errno . ") " . conn->error;
			}
			$row = $res->fetch_assoc();
			$this->flag=0;
			
			return $row['_p_out'];
		
			
	}  
  
  $output = ExecuteProcedure($SQLString);
  
  // After calling the above ExecuteProcedure function the data will insert to the database 
  
  
-- Enjoy Coding -----
  
   
   
?>

