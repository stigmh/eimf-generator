<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <title>PHP sort arrays by property</title>
  
  <link rel="stylesheet" href="../js/highlight/styles/default.css" />
  <script type="text/javascript" src="../js/highlight/highlight.pack.js"></script>
  <script type="text/javascript">hljs.initHighlightingOnLoad();</script>
</head>
<body>

<h1>PHP sort arrays by property</h1>

<p class="date">2010-11-11</p>

<p>If you look at PHP's official <a href="http://no.php.net/manual/en/array.sorting.php" target="_blank">comparison of array sorting functions</a>, you'll see that there are several sort functions to sort an array based upon the array member values. However, they don't give you the opportunity to sort multidimensional arrays. At least not without doing some complex tweaking of the functions.</p>

<p>I've found a way that gives you complete control over your sorting mechanism and allows you to easily sort multidimensional arrays. The technique is similar to the way you do it in Java. Even the predefined sorting functions in PHP is using the same technique, but hides it from you.</p>

<p>Let's write our very own customized sort mechanism; we'll have to create a new function and make it look something like this:</p>

<code><pre class="php">// Create a multidimensional array, with two random selected
// integers as values
$myArray = array(array(345, 569), array(123, 456));

// sort the array with our mechanism, second parameter is the name of
// our custom sorting mechanism
usort($myArray, 'compareTo');

// create our sorting mechanism
function compareTo($x, $y) {
  // $x[0] &lt;=&gt; $myArray[][0] (same with $y)

  if ($x[0] == $y[0]) {
    // they're equal
    return 0;
  }
  else if ($x[0] &lt; $y[0]) {
    // $y[0] is greater
    return 1;
  } else {
    // $x[0] is greater
    return -1;
  }
}</code></pre>

<p>The multidimensional array (<em>$myArray</em>) and the <em>usort()</em> method are obvious. But what about the sort function, how does it work?</p>

<p>usort() is using your custom sort function (<em>compareTo()</em>) to compare two objects of your array and sort your array based upon the result. In this case, <em>usort()</em> runs <em>compareTo()</em> like this: <em>compareTo($myArray[0], $myArray[1]);</em>. Our custom <em>compareTo()</em> is using the first value of each parameter array (<em>$myArray[0][0] &gt;&lt;== $myArray[1][0]</em>) to return either 0, 1 or -1.</p>

<p>If you want to sort the array based upon the second value instead, just replace $x[0] and $y[0] in <em>compareTo()</em> with $x[1] and $y[1]. In that way, you can compare any field in any multidimensional array as you want. It even support strings (<em>$x['myString']</em>)!</p>

<p>The remaining thing to cover is the return value (-1, 0 or 1). 0 means that the objects are equal (the same), 1 that $x is larger than $y and -1 that $y is larger than $x. The <em>compareTo()</em> in the example above sorts the array descending (largest number at top). Just swap the 1 and -1 return result if you want it to be the opposite (ascending). Another way to do it is to replace the &lt; with an &gt;.</p>

<p>You should now understand how it works and be able to modify it so you can sort anything however you want. This also works for one dimensional arrays. The last thing I should cover is sorting when the values are strings. Whether you sort it alphabetically, based upon string length or something entirely else is up to you. In this example, we're doing it the most common way (alphabetically):</p>

<pre><code class="php">// Create a multidimensional array, with a string as the sorting value
$myArray = array(array('some content', 'abdc'), array('other content', 'abc'));

usort($myArray, 'alphaStringSort');

// create our sorting mechanism
function alphaStringSort($x, $y) {
  // $x[1] &lt;=&gt; $myArray[][1] (same with $y)
  if ($x[1] == $y[1]) {
    // they're equal
    return 0;
  }
  else {
    $sortArray = array($x[1], $y[1]);
    asort($sortArray); // alphabetical sort

    if ($sortArray[0] == $x[1]) {
      return 1; // $x[1] is greater (in this case; 'abc')
    } else {
      return -1; // $y[1] is greater
    }
  }
}</code></pre>

</body>
</html>