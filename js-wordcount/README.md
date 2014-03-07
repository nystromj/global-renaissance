Put the contents of this directory into the RenaissanceNLP/data folder. It will access an internal folder called "data," which contains a JSON data file with the pathnames of the raw content.

The index.html contains the Javascript code that will iterate through each item in the JSON files, load the raw html file that it points to, and build a word-frequency table. It also outputs that to the html body for convenience.

It would be easy, once this is complete, to save the data in JSON format for later accessing; if the data that it generates is accurate and useful.