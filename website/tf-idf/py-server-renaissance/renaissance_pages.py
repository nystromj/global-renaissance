import os, traceback, json, re
import renaissance_corpus as rc
from renaissance_corpus import Article

try:
    from django.http import HttpResponse
except:
    print "Couldn't find Django, maybe ok..."

import helper

ANALYSIS_CL_CONTENT   = 'Cleaned Content'
ANALYSIS_ORIG_CONTENT = 'Original Content'
ANALYSIS_WORD_CNTS    = 'Word Counts'
ANALYSIS_WORDNET_DEFS = 'WordNet Definitions'
ANALYSIS_NER_ST       = 'NER Results - Stanford'
ANALYSIS_NER_MA       = 'NER Results - MA'
ANALYSIS_ALL_CONTENT  = 'Content'

from rnlp_common import CONTENT_EXT, HDR_EXT, IDX_DIR, ALL_HDRS_SUMMARY, \
    OUT_RAW_DIR, OUT_NER_DIR

class RNLPWebContext:
    
    def __init__(self):
        # For caching pages
        self.state = {}
        self._articles = []
        self._entry_map = {}
        self._ner_results = {}

    def get_articles(self):
        if not self._articles:
            articles = [f for f in os.listdir(OUT_RAW_DIR) if f.endswith(CONTENT_EXT)]
            # Just for now to keep it manageable
            articles = articles[:100]
            articles.sort()
            self._articles = articles
        return self._articles

    def get_entry_map(self):
        if not self._entry_map:
            hdr_summary = open(ALL_HDRS_SUMMARY, 'r').read()
            self._entry_map = json.loads(hdr_summary)
            print 'Done loading hdr files'
        return self._entry_map
    
    def get_NER_results(self):
        if not self._ner_results:
            # Matches the pattern:
            #   A03192_HEYWOOD_A curtaine lecture as it is read by a countrey farmers wife_MA_NER.json
            #   A03192_HEYWOOD_A curtaine lecture as it is read by a countrey farmers wife_ST_NER.json
            ner_re = re.compile('^([A-Z][0-9]+)_.+_(ST|MA)_NER.json$')
            for r in os.listdir(OUT_NER_DIR):
                m = ner_re.search(r)
                if m:
                    entry_id = m.group(1)
                    ST_MA = m.group(2)
                    ner_results = self._ner_results.setdefault(ST_MA, set())
                    ner_results.add(entry_id)
        return self._ner_results

_RNLP_ctxt = None
def _get_rnlp_ctxt():
    global _RNLP_ctxt
    if _RNLP_ctxt is None:
        _RNLP_ctxt = RNLPWebContext()
    return _RNLP_ctxt

LINKS = [
    ANALYSIS_ORIG_CONTENT,
    ANALYSIS_WORD_CNTS,
    ANALYSIS_WORDNET_DEFS,
    ANALYSIS_NER_ST,
    ANALYSIS_NER_MA,
    #ANALYSIS_MERGE_NER
]

def view_page(req):
    RNLP_ctxt = _get_rnlp_ctxt()
    qry_str = req.REQUEST
    print 'REQUEST:', req.path, qry_str
    
    article_name = qry_str.get('article')
    search_qry   = qry_str.get('qry')
    session = req.session    	
    
    if req.path.endswith('map'):
    	html = open('renaissance/map.html', 'r').read()
    	return HttpResponse(html)
    
    elif req.path.endswith('getcountries'):
    	html = open('renaissance/getcountries.html', 'r').read()
    	return HttpResponse(html)
    
    elif req.path.endswith('custom'):   		
   		if search_qry is not None: 
   			my_results = custom_search(search_qry, 2000)
   			return HttpResponse(my_results, "application/json")
   		else:
   			html = 'Invalid Path'
   
    elif req.path.endswith('author'):   		
   		if search_qry is not None: 
   			my_results = author_search(search_qry, 2000)
   			return HttpResponse(my_results)
   		else:
   			html = 'Invalid Path'
   				
    elif req.path.endswith('main'):
        html = _create_article_list()
        #html = 'Invalid for now'

    elif req.path.endswith('search'):
        html = open('renaissance/page_search.html', 'r').read()
        
        if search_qry is not None:
            
            # create a copy
            rslt_links = list(LINKS)
            rslt_links.insert(1, ANALYSIS_CL_CONTENT)
            
            ner_results = RNLP_ctxt.get_NER_results()
            rslts = do_search(search_qry, 2000)
            formatted_rslts = [ '<b>Total of %d hits.</b>' % len(rslts) ]
            for score, _doc, entry in rslts:
                author = entry['prim_author']
                title = entry['full_title']
                
                if len(title) > 200:
                    last_sp = title[:200].rindex(' ')
                    title = title[:last_sp]
            
                short_title = entry['short_title']
                entry_id = entry['entry_id']
                link_href = """<a href="javascript:void(0)" onClick="refresh_article('%s', '%s')">%s</a>"""
                
                links = []
                for link in rslt_links:
                    # Don't add these links if we haven't done NER analysis on them
                    if link == ANALYSIS_NER_ST and entry_id not in ner_results['ST']:
                        continue
                    if link == ANALYSIS_NER_MA and entry_id not in ner_results['MA']:
                        continue
                    links.append(link_href % (short_title, link, link))
                    
                links = '&nbsp;&nbsp;&nbsp;'.join(links)
                
                out = """<result><b>%s...</b><br>%s [%.4f]<br>%s</result>""" % (title, author, score, links)
                formatted_rslts.append(out)

            srch_rslts = '<br>&nbsp;<br>'.join(formatted_rslts)
        else:
            srch_rslts = ''
            
        html = html.replace('__SEARCH_RESULTS__', srch_rslts)

    elif article_name is not None:
    	print "article name is not none"
        try:
            dataset = qry_str.get('ds')
            
            current_article = session.get('current_article')
            if current_article is None or dataset is None or article_name != current_article.name:
                rootdir = OUT_RAW_DIR
                fname = rootdir+'/'+article_name+CONTENT_EXT
                article_json = RNLP_ctxt.state.get(fname)
                if not article_json:
                    article_json = open(fname, 'r').read()
                    RNLP_ctxt.state[fname] = article_json
                
                metadata_file = fname.replace(CONTENT_EXT, HDR_EXT)
                current_article = Article(article_name, article_json, fname, metadata_file)
                session['current_article'] = current_article
                html = current_article.raw_html

            html = _create_analysis_page(current_article, dataset)
            
        except Exception as e:
            st = traceback.format_exc()
            exc = 'Problem parsing [%s]:\n%s\n%s' % (article_name, e, st)
            #exc = 'Problem parsing [%s]:\n%s' % (article, st)
            html = '<pre>%s</pre>' % exc.replace('\n', '<br>')

    else:
        html = 'Invalid Path'
    
    return HttpResponse(html)

def do_search(qry, limit):
    helper.initPyLucene()
    RNLP_ctxt = _get_rnlp_ctxt()
    entry_map = RNLP_ctxt.get_entry_map()

    from org.apache.lucene.index import DirectoryReader
    from org.apache.lucene.search import IndexSearcher
    from org.apache.lucene.queryparser.classic import QueryParser
    from org.apache.lucene.analysis.standard import StandardAnalyzer
    from org.apache.lucene.store import FSDirectory
    from org.apache.lucene.util import Version
    from java.io import File
    
    print os.path.abspath(os.path.pardir)
    
    reader = DirectoryReader.open(FSDirectory.open(File(IDX_DIR)))
    searcher = IndexSearcher(reader)
    analyzer = StandardAnalyzer(Version.LUCENE_40)

    field = 'contents'
    parser = QueryParser(Version.LUCENE_40, field, analyzer);

    query = parser.parse(qry);
    print 'Searching for:', query.toString(field)
    raw_results = searcher.search(query, limit)
    
    hits = raw_results.scoreDocs
    numTotalHits = raw_results.totalHits
    print numTotalHits, 'total matching documents'
    results = []
    for hit in hits:
        doc = searcher.doc(hit.doc);
        entry_id = doc.get('entry_id')

        entry = entry_map.get(entry_id)
        #print 'entry:', entry
        score = hit.score
        #print 'Hit:', entry['short_title'], score
        results.append((score, doc, entry))
        
    return results
    
def custom_search(qry, limit):
    helper.initPyLucene()
    RNLP_ctxt = _get_rnlp_ctxt()
    entry_map = RNLP_ctxt.get_entry_map()
    rootdir = OUT_RAW_DIR

    from org.apache.lucene.index import DirectoryReader
    from org.apache.lucene.search import IndexSearcher
    from org.apache.lucene.queryparser.classic import QueryParser
    from org.apache.lucene.analysis.standard import StandardAnalyzer
    from org.apache.lucene.store import FSDirectory
    from org.apache.lucene.util import Version
    from java.io import File
    
    reader = DirectoryReader.open(FSDirectory.open(File(IDX_DIR)))
    searcher = IndexSearcher(reader)
    analyzer = StandardAnalyzer(Version.LUCENE_40)

    field = 'contents'
    parser = QueryParser(Version.LUCENE_40, field, analyzer);

    query = parser.parse(qry);
    print 'Searching for:', query.toString(field)
    raw_results = searcher.search(query, limit)
    
    hits = raw_results.scoreDocs
    numTotalHits = raw_results.totalHits
    print numTotalHits, 'total matching documents'
    print rootdir
    
    results = {}
    for hit in hits:
    	doc = searcher.doc(hit.doc)
    	entry_id = doc.get('entry_id')
    	
    	entry = entry_map.get(entry_id)
    	
    	short_title = entry['short_title']
    	year = entry['publ_year']
    	
      fname = short_title + CONTENT_EXT
      results[fname] = year;
    
    f = open ('/Users/Nelle/Documents/coding/text_analysis/newsvn/RenaissanceNLP/data/dataResults/paths/' + qry.replace('"', '') + '.json', 'w')
    f.write(json.dumps(results))
    f.close()
    return json.dumps(results)
           
def author_search(qry, limit):
    helper.initPyLucene()
    RNLP_ctxt = _get_rnlp_ctxt()
    entry_map = RNLP_ctxt.get_entry_map()
    rootdir = OUT_RAW_DIR

    from org.apache.lucene.index import DirectoryReader
    from org.apache.lucene.search import IndexSearcher
    from org.apache.lucene.queryparser.classic import QueryParser
    from org.apache.lucene.analysis.standard import StandardAnalyzer
    from org.apache.lucene.store import FSDirectory
    from org.apache.lucene.util import Version
    from java.io import File
    
    reader = DirectoryReader.open(FSDirectory.open(File(IDX_DIR)))
    searcher = IndexSearcher(reader)
    analyzer = StandardAnalyzer(Version.LUCENE_40)

    field = 'contents'
    parser = QueryParser(Version.LUCENE_40, field, analyzer);

    query = parser.parse(qry);
    print 'Searching for:', query.toString(field)
    raw_results = searcher.search(query, limit)
    
    hits = raw_results.scoreDocs
    numTotalHits = raw_results.totalHits
    print numTotalHits, 'total matching documents'
    
    results = {}
    for hit in hits:
    	doc = searcher.doc(hit.doc)
    	entry_id = doc.get('entry_id')
    	
    	entry = entry_map.get(entry_id)
    	
    	short_title = entry['short_title']
    	print(entry['prim_author'])
        
        if qry in entry['prim_author'].lower():
     	
             fname =  short_title + CONTENT_EXT
             results[entry_id] = {'title': short_title, 'file': fname }
    
    f = open ('/Users/Nelle/Documents/coding/text_analysis/newsvn/RenaissanceNLP/data/dataResults/authorPaths/' + qry + '.json', 'w')
    f.write(json.dumps(results))
    f.close()
    return json.dumps(results)

def _create_article_list():
    RNLP_ctxt = _get_rnlp_ctxt()
    #'<a href="#" onClick="refresh(\'%s\')">%s</a>' % (f,f)
    articles = ['<option>%s</option>' % f.replace(CONTENT_EXT, '') 
                for f in RNLP_ctxt.get_articles()]
    datasets_html = '\n'.join(['<option>%s</option>' % d for d in LINKS]) 
    
    print 'DIR:', os.path.abspath(os.path.pardir)
    page = open('renaissance/page.html', 'r').read()
    # Need some better way of indicating what the article is
    return page.replace('__OPTIONS__', '\n'.join(articles)).replace('__DATASETS__', datasets_html)

def _create_analysis_page(article, dataset):
    assert(isinstance(article, Article))

    article_name = article.name
    processed_sentences = article.processed_sentences
    
    if dataset is None:
        metadata = article.metadata
        print 'metadata:', metadata
        
        title = metadata.get('full_title')
        nlen = min(45, len(title))
        title = title[:nlen]
        year = metadata.get('publ_year')
        author = metadata.get('prim_author')

        hdr = '<b>%s</b>, <b>Title:</b> %s, <b>Author:</b> %s, <b>Date:</b> %s, <b># of Sentences:</b> %d' \
            % (article_name, title, author, year, len(processed_sentences)) 
        html = hdr

    elif dataset == ANALYSIS_CL_CONTENT:
        html = article.raw_html

    elif dataset == ANALYSIS_ORIG_CONTENT:
        original_content_path = os.path.join(helper.get_root_dir(), article.metadata['rel_content_path'])
        html = open(original_content_path, 'r').read()

    elif dataset == ANALYSIS_WORDNET_DEFS:
        wd_cnts = rc.get_word_counts(article.word_counts, mincnt=5)
        terms = wd_cnts.keys()
        # ignore bigrams or more
        terms = filter(lambda t: ' ' not in t, terms)
        
        unidentified = rc.get_unk_wds(terms)
        unidentified_html_ = ['%s<b>%s</b> - %s' % ('', unk, wd_cnts[unk]) for unk in unidentified]
        unidentified_html = ', '.join(unidentified_html_)
        
        indent = 4*'&nbsp;'
        terms_w_defs = rc.get_wd_defs(terms)
        identified_html = ''
        for wd in terms_w_defs:
            wordnet_defs = terms_w_defs[wd]
            identified_html += '<br><b>%s</b> - %s<br>%s%s<br>' \
                  % (wd, wd_cnts[wd], indent, ('<br>'+indent).join(wordnet_defs))
        html = '''
<div id="unidentified_terms"><i><u>Unidentified terms</u></i><br>%s</div><br>
<div id="identified_terms"><i><u>Wordnet Definitions</u></i><br>%s</div> 
''' % (unidentified_html, identified_html)

    elif dataset == ANALYSIS_WORD_CNTS:
        word_cnts = rc.get_word_counts(article.word_counts, mincnt=5)

        # Why am I making this a string? Need to do this for the sake
        # of JSON serialization issues...
        word_cnts = dict([(c, repr(word_cnts[c])) for c in word_cnts.keys()])
        
        #wcs.reverse()
        #html = ' '.join(['(%s: %s)' % (r[0], r[1]) for r in wcs])
        #wcs = dict(wcs)
        
        # working on this will just be something cute, but really should try
        # and get to something more meaningful, textually?  
        json_rslt = json.dumps(word_cnts, ensure_ascii=False)

        # Need to create JSON and then page
        html = json_rslt

    elif dataset == ANALYSIS_NER_ST:
        named_entities = article.get_ner_results(ner='ST')
        html = '<div>%s %s</div>' % (NER_ST_LEGEND, named_entities)

    elif dataset == ANALYSIS_NER_MA:
        named_entities = article.get_ner_results(ner='MA')
        html = '<div>%s %s</div>' % (NER_MA_LEGEND, named_entities)

    else:
        html = ''
    
    return html

# Probably don't care about the organization in this case
NER_ST_LEGEND = '''<div id="legend">
<person><b>Person</b></person>
<location><b>Location</b></location>
<organization><b>Organization</b></organization>
</div>'''

NER_MA_LEGEND = '''<div id="legend">
<person><b>Entity</b></person>
</div>'''
