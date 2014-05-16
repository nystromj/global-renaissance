## Generating TF-IDF files: 

1. **[py-server-renaissance]** Modify _renaissance_pages.py_ to add URLs of the form "/renaissance/custom?qry=[term]"
  - calls custom search function: queries for given term and generates a JSON file called [term].json that contains the path files for each of the top 2,000 hits in the data/Global Renaissance/raw directory.
  - [term].json is stored in the data/dataResults/paths directory.
  - each filename is stored with the year that it was published.

2. **[py-server-renaissance]** Modify _renaissance_pages.py_ to add URL "/renaissance/getcountries" 
  - opens _getcountries.html_ doc which contains javascript that performs custom search in step 1 for every country in data/countries.js
  - data/dataResults/paths directory now contains json file for each country
  
3. **[data]** In RenaissanceNLP/data: new file _CreateFiles.php_ goes through every country json file in the dataResults/paths directory. For each country json, _CreateFiles.php_: 
  - goes through each file name and extracts the correct JSON file from the Global Renaissance/raw directory
  - loops through each raw JSON File and concatenates each paragraph within the file text that contains the current country
  - dumps the concenated paragraphs into a .txt file in the dataRresults/concatParas directory. 

4. **[hadoop_tfidf]** Compile _dooplogs.jar_ from src/ directory with _build.xml_ and upload onto AWS Elastic MapReduce
  - upload .txt files in data/dataResults/concatParas into an S3 Bucket
  - run elastic map reduce to obtain a set of files of the form _part-r-0000*_
  - download result files into dataResults/hadoop_results directory

5. **[data]** In RenaissanceNLP/data: new file _ParseHadoop.php_ pulls each country out of the hadoop results files 
  - formats each line into csv
  - dumps csv into a new file under the dataResults/commaSep directory

6. **[data]** In RenaissanceNLP/data: new file _OrderFiles.php_ sorts each country's tf-idf csv file by tf-idf score 
  - text for each country is filtered for stopwords in _Stopwords.php_
  - resulting files are JSON encoded and put into dataResults/sorted directory
  
7. **[data]** In RenaissanceNLP/data: new file _TopWords.php_ grabs top 100 words from each sorted file in dataResults/sorted
  - resulting files are JSON encoded and put into dataResults/topWords directory 

8. **[data]** In RenaissanceNLP/data: new file _AddCategories.php_ grabs the category classifications for each of the top 100 words for all countries from a database and appends them to each entry in each file of the dataResults/topWords directory

9. **[data]** In RenaissanceNLP/data: new file _FinalData.php_ goes through top ten words for each country. For each word, _FinalData.php_:
  - determines the category of the word (which was added to the `dataResults/topWords/` directory file by _AddCategories.php_)
  - adds the word's score to the categories aggregate score, and appends the word into the categorie's list of relevant words
  - extracts data from the `dataResults/yearFiles/` directory and adds each word's year information to the given category
  - creates a json object with a field for each category. Each category field points to an object that contains the tf-idf score for that category, a list of all words in the category, and the number of occurences of that category across the range of years.
  - dumps the json objects into the dataResults/finalData directory

## Generating year data heatmap: 

1. **[data]** In RenaissanceNLP/data: new file _YearFiles.php_ goes through every country json file in the dataResults/paths directory. For each country json, _YearFiles.php_: 
  - goes through each file name and extracts the correct JSON file from the Global Renaissance/raw directory
  - loads the top words for the current country from the dataResults/topWords directory
  - loops through each paragraph in the raw JSON File and checks whether any of the top words appear
  - if a top word is present, publication year for that file is concatenated to the word in a results object
  - dumps the result object into a json encoded .txt file in the dataRresults/yearCounts directory.

