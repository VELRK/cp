const mysql = require('mysql2/promise');
require('dotenv').config();

async function test() {
  try {
    const pool = mysql.createPool({
      host: process.env.CI_DB_HOST || '127.0.0.1',
      user: process.env.CI_DB_USER || 'root',
      password: process.env.CI_DB_PASS || '',
      database: process.env.CI_DB_NAME || 'cp_web',
      port: Number(process.env.CI_DB_PORT) || 3306,
    });

    console.log('Connecting to', process.env.CI_DB_NAME || 'cp_web');
    const [rows] = await pool.query('SELECT * FROM nb_users LIMIT 1');
    console.log('Success, found users:', rows.length);
    process.exit(0);
  } catch (error) {
    console.error('DB Error:', error);
    process.exit(1);
  }
}
test();
