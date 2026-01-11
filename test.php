<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <?php
$iterations = 10;
$num1 = 0;
$num2 = 1;
for ( $i=2; $i < = $iterations; $i++ )
{
$sum = $num1 + $num2;
$num1 = $num2;
$num2 = $sum;
?>
< tr <?php if ( $i % 2 != 0 ) echo ‘ class=”alt”’ ?> >
< td > F < sub > <?php echo $i?> < /sub > < /td >
< td > <?php echo $num2?> < /td >
< /tr >
<?php
}
?>
</body>
</html>