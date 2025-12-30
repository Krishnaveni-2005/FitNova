const mysql = require('mysql2');

// Create the connection pool. The pool allows sending multiple queries
// and handling the connection management automatically.
const pool = mysql.createPool({
    host: 'localhost',
    user: 'root',      // Replace with your MySQL username
    password: '',      // Replace with your MySQL password
    database: 'fitnova_db',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0
});

// Promisify for Node.js async/await usage
const promisePool = pool.promise();

module.exports = promisePool;
