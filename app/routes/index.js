var express = require('express');
var path = require('path');

var router = express.Router();

/* GET home page. */
router.get('/', function(req, res) {
  res.render('index', { title: 'Homepage' });
});

/* GET home page. */
router.get('/map', function(req, res) {
  res.render('map/map', { title: 'Map' });
});

/* GET home page. */
router.get('/lda', function(req, res) {
	var country = req.query.country ? req.query.country + "" : "America";
	res.render('lda/lda', { country: country });
});

router.get('/downloads', function (req,res) {
	if (req.query.file) {
		var filename = 'global-renaissance-' + req.query.file + '.tar.gz';
		var download = path.resolve(__dirname + '/../../public/downloads/' + filename);
		res.download(download);
	}
	else {
		res.render('download');
	}
});

module.exports = router;
