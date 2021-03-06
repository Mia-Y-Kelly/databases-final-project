<?php
    require "db.php";
	session_start();
?>

<html>
    <body>
        <form action="login.php" method="post">
            <?php
                if (!isset($_SESSION['username'])) {
            ?>
                <input type="submit" class="submit" value='login' name="login">
            <?php
                } else {
                    echo "Welcome ". $_SESSION['username'];
            ?>
                    <input type="submit" class="submit" value='logout' name="logout">
            <?php
                }
            ?>
		</form>

		<form id="roster" name="roster" method="post" action="instructor.php">  
			<p>View Class Roster</p>
			<select Emp Name='current_class'>  
            <option value="">--- Select Class ---</option>  
			<?php
				try {
						$dbh = connectDB();
						$account = $_SESSION['username'];
						$sql = "SELECT course_id FROM Teaches WHERE instr_acc='$account'"; 
						$statement = $dbh->prepare($sql);
						$result = $statement->execute();
						$row = $statement->fetchAll(PDO::FETCH_COLUMN);
        				$dbh = null;
						// Iterating through the product array
        				foreach($row as $item){
            				echo "<option value='$item'>$item</option>";
        				}
				} catch (PDOException $e) {
						print "<br/>ERROR: ". $e->getMessage()."<br/>";
						die();
				}
			?>
				<input type="submit" id="show_roster" name="show_roster" value="Select Class" />
        	<?php 
				if(isset($_POST['show_roster'])){
					try {
						$class = $_POST['current_class'];
						$dbh = connectDB();
						$statement = $dbh->prepare("SELECT stu_acc, course_id FROM Takes where course_id='$class';");
						$result = $statement->execute();
						$rows = $statement->fetchAll(PDO::FETCH_COLUMN);
						$dbh = null;
						
						
						// Print out all the names in a list
						echo "<ul>";
						foreach($rows as $name) {
							echo "<li>$name</li>";
						}
						echo "</ul>";
						return;	
	                } catch (PDOException $e) {
                        print "<br/>ERROR: ". $e->getMessage()."<br/>";
                        die();
    	            }
				}
			?>
		</form>
		
		<!--Show survey results -->
		<form id="survey" name="survey" method="post" method="instructor.php">
            <p>View Courses Results</p>
            <select Emp Name='current_survey'>
            <option value="">--- Select Class ---</option>
            <?php
                try {
                        $dbh = connectDB();
                        $account = $_SESSION['username'];
                        $sql = "SELECT course_id FROM Teaches WHERE instr_acc='$account'";
                        $statement = $dbh->prepare($sql);
                        $result = $statement->execute();
                        $row = $statement->fetchAll(PDO::FETCH_COLUMN);
                        $dbh = null;
                        
						// Iterating through the product array
                        foreach($row as $item){
                            echo "<option value='$item'>$item</option>";
                        }
                } catch (PDOException $e) {
                        print "<br/>ERROR: ". $e->getMessage()."<br/>";
                        die();
                }
            ?>
                <input type="submit" id="show_survey_results" name="show_survey_results" value="Show Results" />
			<?php
                if(isset($_POST['show_survey_results'])){
                    try {
						$class = $_POST['current_survey'];
						$dbh = connectDB();
						
						// Retrieve questions
						$questions = $dbh->prepare("SELECT question_number, question FROM Question;");
						$question_result = $questions->execute();
						$all_q = $questions->fetch();

						// Retrive choices and frequencies
						$questions = $dbh->prepare("SELECT question_number, choice, freq from Course_Question_Responses WHERE course_id='$class' AND essay='';");
						$question_result = $questions->execute();						
						$questions_arr = $questions->fetchAll();
						$question_total_responses[] = 1;
						$counter = 1;
						$total =0;
						
						// Calculate total frequency per question
						for($i = 0; $i < count($questions_arr); $i++) {
							// Add to total frequency
							$q = $questions_arr[$i];
							if($q[0] == $counter) {
								$total = $total + $q[2];
							} else {
								// Push total
								array_push($question_total_responses, $total);
								
								// Start summing new frequency
								$total = 0;
								$total = $total + $q[2];
								$counter++;
							}
								
						}
						
						// Push last element in the array
						array_push($question_total_responses, $total);	
						array_shift($question_total_responses); 	// This was set to 1 to create array but is not needed anymore
						var_dump($question_total_responses);
						
						// Print all the question
						echo "<table/><tr><th>Response Option</th><th>Frequency</th><th>Percent</th></tr>";
						foreach($questions_arr as $q) {
							$freq = round((($q[2] / $question_total_responses[$q[0] - 1]) * 100), 0);
							echo "<tr><td>".$q[1]."</td><td>".$q[2]."</td><td>".$freq.".00%<td></tr>";
						}
						echo "</table>";
						// Debug
						//var_dump($questions_arr)
						$counter = 0;
						foreach($questions_arr as $q) {
							print "<br/>";
                        	var_dump($questions_arr[$counter]);
							$counter++;
						}
						
                    } catch (PDOException $e) {
                        print "<br/>ERROR: ". $e->getMessage()."<br/>";
                        die();
                    }
                }
            ?>
		</form>
    </body>
</html>
