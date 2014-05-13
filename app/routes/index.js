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

module.exports = router;
