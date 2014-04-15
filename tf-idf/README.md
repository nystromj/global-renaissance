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
  
7. **[data]** In RenaissanceNLP/data: new file _TopTen.php_ grabs top ten words from each sorted file in dataResults/sorted
  - resulting files are JSON encoded and put into dataResults/topTen directory 

8. **[data]** In RenaissanceNLP/data: new file _GetContinents.php_ goes through top ten words for each country. For each word, _GetContinents.php_:
  - calculates the density of the word across the texts for each continent in _Continents.php_
  - calculates the density of the word across the milton and shakespeare corpuses in the dataResults/authors directory (see below for details on how these files are generated)
  - creates a json object with the densities for each continent, each author and across all the results
  - dumps the json objects into the dataResults/finalData directory
  
9. **[data]** In RenaissanceNLP/data: new file _MakeTSV.php_ goes through each file in dataResults/finalData and converts it to a .tsv for the visualization heatmap
  - each tsv is of the form "word collection  density" where collection is the continent, corpus or author
  - resulting tsv files are stored in dataResults/tsvFiles directory

## Milton and Shakespeare: 

1. **[py-server-renaissance]** Modify _renaissance_pages.py_ to add URLs of the form "/renaissance/author?qry=[name]"
  - calls custom search function: queries for given name and generates a JSON file called [name].json that contains the path files for each of the results with the given name as the primary author.
  - [name].json is stored in the data/dataResults/authorPaths directory.

2. **[data]** In RenaissanceNLP/data: new file _AuthorFiles.php_ goes through every author JSON file in the dataResults/authorPaths directory. For each author json, _AuthorFiles.php_:  
  - goes through each file name and extracts the correct JSON file from the Global Renaissance/raw directory
  - loops through each raw JSON File and concatenates each paragraph within the file text
  - dumps the concenated paragraphs into a .txt file in the dataRresults/literature directory
  
3. **[hadoop_tfidf]** Use dooplogs.jar on a local hadoop instance to generate tf-idf results for each author
  - upload .txt files in data/dataResults/literature into HDFS
  - run dooplogs.jar to obtain a set of files of the form _part-r-0000*_
  - download result files into dataResults/hadoopResults directory
  
4. **[data]** In RenaissanceNLP/data: new file _ParseHadoop.php_ pulls each author out of the hadoop results files 
  - formats each line into csv
  - dumps csv into a new file under the dataResults/authors directory
  
5. tf-idf results in dataResults/authors is used by _GetContinents.php_ in Step 8 of __Generating TF-IDF Files_ above.  

## Generating year data heatmap: 

1. **[data]** In RenaissanceNLP/data: new file _YearFiles.php_ goes through every country json file in the dataResults/paths directory. For each country json, _YearFiles.php_: 
  - goes through each file name and extracts the correct JSON file from the Global Renaissance/raw directory
  - loads the topTen words for the current country from the dataResults/topTen directory
  - loops through each paragraph in the raw JSON File and checks whether any of the top ten words appear
  - if a top word is present, publication year for that file is concatenated to the word in a results object
  - dumps the result object into a json encoded .txt file in the dataRresults/yearCounts directory.

1. **[data]** In RenaissanceNLP/data: new file _ParseYears.php_  goes through every country json file in the dataResults/yearCounts directory. For each country, _ParseYears.php_: 
  - loops through each of the years for the top words and extracts the first four digits for each year (hacky method for year extraction will be improved later)
  - assigns each year to a bucket between 1550-1700 by 50 year intervals
  - returns a tsv file for each country of the form "word bucket  frequency"
  - tsv files saved in the dataResults/yearTsv directory for heatmap