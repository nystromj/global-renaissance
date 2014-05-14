var express = require('express');
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
  res.render('lda/lda');
});

module.exports = router;
