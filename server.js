// Start up the server
var express = require('express');
var simpleFsRest = require('simple-fs-rest');

var app = express();
var PORT = process.env.port || 8080;
app.set('view engine', 'ejs');

// Static content
app.use(express.static('./app'));

// REST API
app.use('/api',simpleFsRest());

app.listen(PORT);
console.log("Listening on port "+PORT);
