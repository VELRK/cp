import mysql from 'mysql2/promise';

export const pool = mysql.createPool({
  host: process.env.CI_DB_HOST || '127.0.0.1',
  user: process.env.CI_DB_USER || 'root',
  password: process.env.CI_DB_PASS || '',
  database: process.env.CI_DB_NAME || 'cp_web',
  port: Number(process.env.CI_DB_PORT) || 3306,
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0,
});
