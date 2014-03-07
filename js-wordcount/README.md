Put the contents of this directory into the RenaissanceNLP/data folder. It will access an internal folder called "data," which contains a JSON data file with the pathnames of the raw content.

The index.html contains the Javascript code that will iterate through each item in the JSON files, load the raw html file that it points to, and build a word-frequency table. It also outputs that to the html body for convenience.

It would be easy, once this is complete, to save the data in JSON format for later accessing; if the data that it generates is accurate and useful.

Within the data folder, you will also see the files wordData.js, stopwords.js, and distinctWords.html. DistinctWords.html uses the data previously collected from the wordcount, and finds the distinct words for each place (E.g. for China, the words that do not appear in the set of words for America, Virginia, and India.)