<!DOCTYPE html>
<html lang="en">

<head>
	<link href="Project4.css" rel="stylesheet" type="text/css">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Police Emergency Service System</title>


    <script>
        const validate = () =>{
            let i = document.forms['form1']['patrolCarId'].value
            if(i == null || i == ''){
                alert('Please enter the Patrol Car ID to continue');
            }
        }
    </script>
    <?php

    if (isset($_POST["btnUpdate"])) {
        require_once 'db.php';


        
        $mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);

        if ($mysqli->connect_errno) {
            die("Error connecting to the database: " . $mysqli->connect_error);
        }

        $sql = "UPDATE patrolcar SET patrolcarStatusId = ? WHERE patrolcarId = ?";


        if (!($stmt = $mysqli->prepare($sql))) {
            die('error preparing you db' . $mysqli->errno);
        }
        if (!($stmt->bind_param('ss', $_POST['patrolCarStatus'], $_POST['patrolCarId']))) {
            die('bind params failed' . $stmt->errno);
        }

        if (!($stmt->execute())) {
            die('Execute failed:' . $stmt->errno);
        }

        if ($_POST['patrolCarStatus'] == 4) {
            $sql = "UPDATE dispatch SET timeArrived = NOW() 
            WHERE timeArrived is NULL and patrolcarId=?";

            if (!($stmt = $mysqli->prepare($sql))) {
                die('error preparing you db' . $mysqli->errno);
            }

            if (!($stmt->bind_param('s', $_POST['patrolCarId']))) {
                die('bind params failed' . $stmt->errno);
            }
            if (!($stmt->execute())) {
                die('Update dispatch failed:' . $stmt->errno);
            }
        } else if ($_POST['patrolCarStatus'] == 3) {
            $sql = "SELECT incidentId FROM dispatch 
            WHERE timeCompleted is NULL and patrolcarId=?";

            if (!($stmt = $mysqli->prepare($sql))) {
                die('error preparing you db' . $mysqli->errno);
            }

            if (!($stmt->bind_param('s', $_POST['patrolCarId']))) {
                die('bind params failed' . $stmt->errno);
            }
            if (!($stmt->execute())) {
                die('Update dispatch failed:' . $stmt->errno);
            }
            if (!($resultset = $stmt->get_result())) {
                die('Getting result set failed: ' . $stmt->errno);
            }

            $incidentId;

            while ($row = $resultset->fetch_assoc()) {
                $incidentId = $row['incidentId'];

                $sql = "UPDATE dispatch SET timeCompleted = NOW() 
                WHERE timeCompleted is NULL and patrolcarId=?";

                if (!($stmt = $mysqli->prepare($sql))) {
                    die('error preparing you db' . $mysqli->errno);
                }

                if (!($stmt->bind_param('s', $_POST['patrolCarId']))) {
                    die('bind params failed' . $stmt->errno);
                }
                if (!($stmt->execute())) {
                    die('Update dispatch failed:' . $stmt->errno);
                }

                
                $sql = "UPDATE incident SET incidentStatusId = '3' WHERE incidentId= '$incidentId' AND 
                NOT EXISTS (SELECT * FROM dispatch WHERE timeCompleted is NULL and incidentId='$incidentId')";

                if (!$stmt = $mysqli->prepare($sql)) {
                    die('Prepare failed 11: ' . $mysqli->errno);
                }
                if (!($stmt->execute())) {
                    die('Update dispatch table failed: ' . $stmt->errno);
                }
            }

            
        }

        $stmt->close();
        if($mysqli->close()){?>
            <script type="text/javascript">window.location="./logcall.php";</script>
        <?php
        };

    }
    

    ?>

</head>

<body>
    <?php require_once "nav.php"; ?>
    <?php

    if (!(isset($_POST['btnSearch']))) {
    ?>
        <div id="container">
            <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="POST" name="form1" onSubmit="return validate();">
                <table class='table'>
                    <tr>
                    
                        <h1 class='table__top' style="width:40%; padding:5px; font-size:18px">

                            Patrol Car ID:
                        </h1>
                    </tr>
                    <tr class='searchForm'>

                        <td class='table__left'><input type="text" name='patrolCarId' id='patrolCarId' placeholder='Patrol ID...' />
                            <button type="submit" name='btnSearch' id='btnSearch'>Search</button>
                        </td>
                        
                    </tr>
                </table>
            </form>
        </div>

        <?php } else {

        require_once 'db.php';


        $mysqli = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DATABASE);
        if ($mysqli->connect_errno) {
            die("Error connecting to the database: " . $mysqli->connect_error);
        }

        
        $sql = "SELECT * FROM patrolcar WHERE patrolcarId = ?";

        
        if (!($stmt = $mysqli->prepare($sql))) {
            die("Prepare failed: " . $mysqli->errno);
        }

        if (!($stmt->bind_param('s', $_POST['patrolCarId']))) {
            die("Binding parameters failed: " . $stmt->errno);
        }

        if (!$stmt->execute()) {
            die("Execute failed: " . $stmt->errno);
        }

        if (!($resultset = $stmt->get_result())) {
            die('Getting result set failed: ' . $stmt->errno);
        }

        
        if ($resultset->num_rows == 0) {
        ?>
            <script>
                window.location = 'update.php';
                alert('no car found')
            </script>"
        <?php }
        

        $patrolCarId;
        $patrolCarStatusId;

        while ($row = $resultset->fetch_assoc()) {
            $patrolCarId = $row['patrolcarId'];
            $patrolCarStatusId = $row['patrolcarStatusId'];
        }

        
        $sql = "SELECT * FROM patrolcar_status";

        if (!($stmt = $mysqli->prepare($sql))) {
            die("Prepare failed: " . $mysqli->errno);
        }

        if (!($stmt->execute())) {
            die("Execute failed: " . $stmt->errno);
        }

        if (!($resultset = $stmt->get_result())) {
            die('Getting result set failed: ' . $stmt->errno);
        }

        $patrolCarStatusArray;

        while ($row = $resultset->fetch_assoc()) {
            $patrolCarStatusArray[$row['statusId']] = $row['statusDesc'];
        }

        $stmt->close();

        $resultset->close();

        $mysqli->close();

        ?>
        <form action="<?php echo htmlentities($_SERVER['PHP_SELF']) ?>" method="post" name='form2'>

            <table width="50%" border="0" align='center' cellpadding='4' cellspacing='4'>
                <tr>
                    <h1 class='table__top' style="width:40%; padding:5px; font-size:20px">
                        Patrol Car Detail
                    </h1>
                </tr>
                <tr>
                    <td>ID: </td>
                    <td><?php echo $patrolCarId ?>

                        <input type="hidden" name="patrolCarId" id="patrolCarId" value="<?php echo $patrolCarId ?>">

                    </td>
                </tr>

                <tr>
                    <td>Status: </td>
                    <td>

                        <select name='patrolCarStatus' id='patrolCarStatus'>

                            <?php
                            foreach ($patrolCarStatusArray as $key => $value) {
                                $isSelected = ($key == $patrolCarStatusId) ?  'selected="selected"' : '';
                                echo '<option value=' . $key . ' ' . $isSelected . ' >
               ' . $value . '
            </option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                
            </table>
            <div class='form__buttons'>
                <button type="reset" name='btnCancel' id='btnCancel'>Reset</button>
                <button type="submit" name='btnUpdate' id='btnUpdate'>Update</button>
            </div>

        </form>


    <?php } ?>




</body>

</html>