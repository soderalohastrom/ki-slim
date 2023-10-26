<?php

// Get the phrase from the form input, or default to 'Hello World'
$phrase = isset($_GET['the_phrase']) ? htmlspecialchars($_GET['the_phrase']) : 'learn something. That is the only thing that never fails. You may grow old and trembling in your anatomies, you may lie awake at night listening to the disorder of your veins, you may miss your only love, you may see the world about you devastated by evil lunatics, or know your honour trampled in the sewers of baser minds. There is only one thing for it then — to learn. Learn why the world wags and what wags it. That is the only thing which the mind can never exhaust, never alienate, never be tortured by, never fear or distrust, and never dream of regretting. Learning is the only thing for you. Look what a lot of things there are to learn';
echo "<p>Phrase: " . $phrase . "</p>";

// Calculate TF-IDF scores and sparse vector
$words = explode(" ", $phrase);
$termFrequency = array_count_values($words);

$sparseVectorIndices = [];
$sparseVectorValues = [];
$index = 0;
foreach ($termFrequency as $term => $tf) {
  $idf = isset($termFrequency[$term]) ? log(count($termFrequency) / $termFrequency[$term]) : 0;
  $tfidfScore = $tf * $idf;

  // Add non-zero values to the sparse vector indices and values
  if ($tfidfScore != 0) {
    $sparseVectorIndices[] = $index;
    $sparseVectorValues[] = $tfidfScore;
  }

  $index++;
}

// Print out the sparse vector indices and values in pretty JSON
$indices = json_encode($sparseVectorIndices, JSON_PRETTY_PRINT);
$values = json_encode($sparseVectorValues, JSON_PRETTY_PRINT);

echo "<pre>Indices: " . htmlspecialchars($indices) . "</pre>";
echo "<pre>Values: " . htmlspecialchars($values) . "</pre>";
?>