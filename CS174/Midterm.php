<!-- John Paul Tran, CS174 Sec 04, MidTerm 1 -->

<?php
    // default echo statement from slides
    echo <<<_END
		<html><head><title>File Upload</title></head><body>
		<form method='post' action='Midterm.php' enctype='multipart/form-data'>
			Select File: <input type='file' name='filename' size='10'>
			<input type='submit' value='Upload'>
		</form>
_END;

if ($_FILES)
{
    if ($_FILES['filename']['type'] == 'text/plain') 
    {
        // retrieve name and move file from temp location to current directory
        $name = $_FILES['filename']['name'];
        move_uploaded_file($_FILES['filename']['tmp_name'], $name);
        echo "Uploaded file '$name'<br>";

        // open, read, and close file
        $file = fopen($name, 'r') or die("ERROR: Cannot open file");
        $content = fread($file, filesize($name));
        fclose($file);

        // sanitize to one line and calculate max product
        $string = str_replace(array("\n", "\r", " "), '', $content);
        calculateMaxProduct($string);
    }
    else 
    {
        echo "ERROR: File must be .txt format<br>";
    }
}
echo "</body></html>";

// function to calculate the max product of four adjacent numbers in 20x20 grid
function calculateMaxProduct($string) 
{
    // make sure the input is correct length and has correct values
    if (strlen($string) != 400 || !is_numeric($string)) 
    {
        echo "ERROR: Wrong file format<br>";
        return -1;
    }

    // create array to hold contents of text file in 20x20 grid
    $array = array();
    for ($x = 0; $x < 20; $x++) 
    {
        $array[$x] = str_split(substr($string, 20 * $x, 20));
    }

    // check for max horizontal product and records its factors
    $maxHorizontalProduct = 0;
    $maxHorizontalFactors = array();
    // iterate thru each row
    for ($x = 0; $x < count($array); $x++) 
    {
        $maxProductInRow = 0;
        $factorsInRow = array();
        // iterate thru each column
        for ($y = 0; $y < count($array[$x]) - 3; $y++) 
        {
            // calculate product
            $product = $array[$x][$y] * $array[$x][$y+1] * $array[$x][$y+2] * $array[$x][$y+3];
            // if current product is greater than max, set to max product in row
            if ($product > $maxProductInRow) 
            {
                $maxProductInRow = $product;
                $factorsInRow = array_slice($array[$x], $y, 4);
            }
        }
        // if max product from last row is greater than current max, set to max
        if ($maxProductInRow > $maxHorizontalProduct) 
        {
            $maxHorizontalProduct = $maxProductInRow;
            $maxHorizontalFactors = $factorsInRow;
        }
    }

    // check for max vertical product and records its factors
    $maxVerticalProduct = 0;
    $maxVerticalFactors = array();
    // iterate thru each column
    for ($y = 0; $y < count($array[0]); $y++) 
    {
        $maxProductInColumn = 0;
        $factorsInColumn = array();
        // iterate thru each row
        for ($x = 0; $x < count($array) - 3; $x++) 
        {
            // calculate product
            $product = $array[$x][$y] * $array[$x+1][$y] * $array[$x+2][$y] * $array[$x+3][$y];
            // if current product is greater than max, set to max product in column
            if ($product > $maxProductInColumn) 
            {
                $maxProductInColumn = $product;
                $factorsInColumn = array($array[$x][$y], $array[$x+1][$y], $array[$x+2][$y], $array[$x+3][$y]);
            }
        }
        // if max product from last column is greater than current max, set to max
        if ($maxProductInColumn > $maxVerticalProduct) 
        {
            $maxVerticalProduct = $maxProductInColumn;
            $maxVerticalFactors = $factorsInColumn;
        }
    }

    // check for max downwards diagonal product and records its factors
    $maxDownwardDiagonalProduct = 0;
    $maxDownwardDiagonalFactors = array();
    // iterate thru each column for diagonal
    for ($y = 0; $y <= count($array[0]) - 4; $y++) 
    {
        $maxProductInDownward = 0;
        $factorsInDownward = array();
        // iterate thru each row for down diagonal
        for ($x = count($array) - 4; $x >= 0; $x--) 
        {
            // calculate product
            $product = $array[$x][$y] * $array[$x+1][$y+1] * $array[$x+2][$y+2] * $array[$x+3][$y+3];
            // if current product is greater than max, set to max product in diagonal
            if ($product > $maxProductInDownward) 
            {
                $maxProductInDownward = $product;
                $factorsInDownward = array($array[$x][$y], $array[$x + 1][$y + 1], $array[$x + 2][$y + 2], $array[$x + 3][$y + 3]);
            }
        }
        // if max product from last diagonal is greater than current max, set to max
        if ($maxProductInDownward > $maxDownwardDiagonalProduct) 
        {
            $maxDownwardDiagonalProduct = $maxProductInDownward;
            $maxDownwardDiagonalFactors = $factorsInDownward;
        }
    }

    // check for max upwards diagonal product and records its factors
    $maxUpwardDiagonalProduct = 0;
    $maxUpwardDiagonalFactors = array();
    // iterate thru each column position for diagonal
    for ($y = 0; $y <= count($array[0]) - 4; $y++) 
    {
        $maxProductInUpward = 0;
        $factorsInUpward = array();
        // iterate thru each row for up diagonal
        for ($x = 3; $x < count($array); $x++) 
        {
            // calculate product
            $product = $array[$x][$y] * $array[$x-1][$y+1] * $array[$x-2][$y+2] * $array[$x-3][$y+3];
            // if current product is greater than max, set to max product in diagonal
            if ($product > $maxProductInUpward) 
            {
                $maxProductInUpward = $product;
                $factorsInUpward = array($array[$x][$y], $array[$x - 1][$y + 1], $array[$x - 2][$y + 2], $array[$x - 3][$y + 3]);
            }
        }
        // if max product from last diagonal is greater than current max, set to max
        if ($maxProductInUpward > $maxUpwardDiagonalProduct) 
        {
            $maxUpwardDiagonalProduct = $maxProductInUpward;
            $maxUpwardDiagonalFactors = $factorsInUpward;
        }
    }

    // finds the max product in the grid
    $maxProduct = max($maxHorizontalProduct, $maxVerticalProduct, $maxDownwardDiagonalProduct, $maxUpwardDiagonalProduct);

    // finds the factors of the max product in the grid
    if ($maxProduct == $maxHorizontalProduct) 
    {
        $maxFactors = $maxHorizontalFactors;
    }
    else if ($maxProduct == $maxVerticalProduct) 
    {
        $maxFactors = $maxVerticalFactors;
    }
    else if ($maxProduct == $maxDownwardDiagonalProduct) 
    {
        $maxFactors = $maxDownwardDiagonalFactors;
    }
    else 
    {
        $maxFactors = $maxUpwardDiagonalFactors;
    }

    // print the max product as well as its four adjacent factors in the grid
    echo "The greatest product of four adjacent numbers in the grid: " . $maxProduct;
    echo "<br>The four adjacent numbers: " . $maxFactors[0] . $maxFactors[1] . $maxFactors[2] . $maxFactors[3] . "<br>";
}

// function to sanitize test cases
function sanitize($string) 
{
    $string = str_replace(array("\n", "\r", " "), '', $string);
    return $string;
}

// tester function for other values
function test()
{
    echo "<br> Test Cases: Greatest Product of Four Adjacent Numbers = 24 if passed<br>";

    // test 1: vertical max
    echo "<br>Test 1: Vertical Max<br>";
    $testOne = "00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000100000000000000
    00000200000000000000
    00000300000000000000
    00000400000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000";
    calculateMaxProduct(sanitize($testOne));

    // test 2: down diagonal max
    echo "<br>Test 2: Down Diagonal Max<br>";
    $testTwo = "00000000000000000000
    00000000000000000000
    00000000000000000000
    00000040000000000000
    00000002000000000000
    00000000100000000000
    00000000030000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000";
    calculateMaxProduct(sanitize($testTwo));

    // test 3: <400 numbers
    echo "<br>Test 3: Less Than 400 Numbers<br>";
    $testThree = "00000000000000000000
    00000000000000000000
    00000000000000000000
    00000010000000000000
    00000002000000000000
    00000000300000000000
    00000000040000000000";
    calculateMaxProduct(sanitize($testThree));

    // test 4: not numeric values
    echo "<br>Test 4: Non-Numeric Values<br>";
    $testFour = "00000000000000000000
    000000000000a0000000
    00000000000000000000
    000000x0000000000000
    0000000u000000000000
    00000000y00000000000
    000000000w0000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    000000s0000000000000
    00000000000000000000
    00000000000b00000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    000000x0000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000";
    calculateMaxProduct(sanitize($testFour));

    // test 5: up diagonal max
    echo "<br>Test 5: Up Diagonal Max<br>";
    $testFive = "00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000200000000000
    00000001000000000000
    00000040000000000000
    00000300000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000";
    calculateMaxProduct(sanitize($testFive));

    // test 6: horizontal max
    echo "<br>Test 6: Horizontal Max<br>";
    $testSix = "00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000000000000000000
    00000002341000000000
    00000000000000000000
    00000000000000000000";
    calculateMaxProduct(sanitize($testSix));
}

test();
?>