<!-- John Paul Tran, CS174 Sec 04, MidTerm 2 -->

<?php
    // include login file to access database
    require_once 'login.php';
    
    $conn = new mysqli($hn, $un, $pw, $db);
    if ($conn->connect_error) die(mysql_fatal_error("MySQL failed to connect."));

    // HTML form section for signing up users
    echo <<<_END
	    <html><body>
		<form method='post' action='Midterm2.php' enctype='multipart/form-data'><pre>
		User Sign Up
		Username: <input type="text" name="username">
		Password: <input type="text" name="password">
		<input type="submit" value="SIGN UP USER" name="submit">
		</pre></form>
_END;
    echo "</body></html>";

    // get input and store into table
    if(isset($_POST['username']) && $_POST['password'])
	{
        // sanitize
		$username = sanitizeMySQL($conn, $_POST['username']);
		$password = sanitizeMySQL($conn, $_POST['password']);

        // create salts to attach to front and back of password for security
        $salt1 = generateSalt();
        $salt2 = generateSalt();
        // use hash function for security
        $token = hash('ripemd128', "$salt1$password$salt2");

        // use placeholders to transfer data to database
        $stmt = $conn->prepare('INSERT INTO credentials VALUES(?, ?, ?, ?)');
		$stmt->bind_param('ssss', $username, $token, $salt1, $salt2);
		$stmt->execute();
        if ($stmt->affected_rows == 0) die(mysql_fatal_error("Database access failed."));
    }

    // HTML form section for user log in
    echo <<<_END
	    <html><body>
		<form method='post' action='Midterm2.php' enctype='multipart/form-data'><pre>
		User Log In
		Username: <input type="text" name="login_username">
		Password: <input type="text" name="login_password">
		<input type="submit" value="LOG IN" name="submit">
		</pre></form>
_END;
    echo "</body></html>";

    // match username and password from data in table
    if(isset($_POST['login_username']) && isset($_POST['login_password']))
    {
        // sanitize
        $username = sanitizeMySQL($conn, $_POST['login_username']);
        $password = sanitizeMySQL($conn, $_POST['login_password']);

        // use place holder to retreive data from database
        $stmt = $conn->prepare('SELECT * FROM credentials WHERE username=?');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        if ($stmt->affected_rows == 0) die(mysql_fatal_error("Database access failed."));

        // check if password matches
        $result = $stmt->get_result();
        $rows = $result->num_rows;
        if ($rows > 0)
        {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            // get the corresponding salts and hash to match password
            $salt1 = $row["salt1"];
            $salt2 = $row["salt2"];
            $token = hash('ripemd128', "$salt1$password$salt2");
            // if passwords match
            if($token == $row["password"])
            {
                // retrieve and display user info
                webpage($conn, $username);
            }
            else
            {
                echo "ERROR: Incorrect username or password!";
            }
        }
        else
        {
            echo "ERROR: Incorrect username or password!";
        }
    }

    // close connection
    $conn->close();

    // function for webpage to upload text file and input a string
    // takes in username as key in table
    function webpage($conn, $username)
    {
        // HTML form section
        echo <<<_END
        <form method='post' action='Midterm2.php' enctype='multipart/form-data'><pre>
                Content Name: <input type="text" name="content_name">
                Select File Content: <input type="file" name="file_content">
                <input type="hidden" name="upload" value="yes">
                <input type="submit" value="SUBMIT" name="submit">
            </pre></form>
_END;

        // get input and store into table
        if (isset($_POST['content_name']) && $_FILES)
        {
            // sanitize
            $name = get_post($conn, 'content_name');

            // switch to ensure text file input
            switch($_FILES['file_content']['type'])
            {
                case 'text/plain' : 
                    $text = 'txt'; 
                    break;
                default: 
                    $text = ''; 
                    break;
            }
            // insert into table
            if ($text)
            {
                $contentFile = $_FILES['file_content']['tmp_name'];
                $file = file_get_contents($contentFile);
                $result = $conn->query("INSERT INTO user_contents VALUES('$username', '$name', '$file')");
                if (!$result) echo "ERROR: Insert failed";
            }
            else 
            {
                echo "ERROR: File must be .txt format<br>";
            }
        }

        // query database
        $result = $conn->query("SELECT * FROM user_contents");
        if(!$result) die("ERROR");
        $rows = $result->num_rows;
        for ($j = 0; $j < $rows; ++$j)
        {
            // read results
            $result->data_seek($j);
            $row = $result->fetch_array(MYSQLI_NUM);
            if($row[0] == $username)
            {
                echo <<<_END
<pre>
Content Name: $row[1]
File Content: $row[2]
</pre>
_END;
            }
        }
        echo "</body></html>";
    }

    // function to generate different salt for each password to ensure security
    function generateSalt()
	{
        // string of random characters
	    $random = 'AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789@!&^?%';
		$salt = ''; // empty string to store generated salt

		for ($j = 0; $j < 6; $j++) 
		{
            // randomize an index to retrieve a character from 'random' string
            $i = rand(0, strlen($random) - 1);
            // concatenate random character to salt
			$salt .= $random[$i];
		}
		return $salt;
    }
    
    // function to display a more user friendly error messagge
    function mysql_fatal_error($msg)
    {
        echo <<< _END
    We are sorry, but it was not possible to complete
    the requested task. The error message was: 

    <p>$msg</p>

    Please click the back button on your browser and try again.
_END;
    }

    // function to strip out any characters that a hacker may have inserted in order to break into or alter your database
    function get_post($conn, $var)
    {
        return $conn->real_escape_string($_POST[$var]);
    }

    // function to sanitize user input for potential HTML injection attacks
    function sanitizeString($var)
    {
        $var = stripslashes($var);
        $var = strip_tags($var);
        $var = htmlentities($var);
        return $var;
    }

    // function to sanitize user input for MySQL queries
    function sanitizeMySQL($conn, $var)
    {
        $var = $conn->real_escape_string($var);
        $var = sanitizeString($var);
        return $var;
    }
?>