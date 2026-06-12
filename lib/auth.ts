import { pool } from './db';

export async function getAuthUser(request: Request) {
  const token = request.headers.get('x-api-token') || 
                request.headers.get('authorization')?.replace(/^Bearer\s+/i, '');

  if (!token) return null;

  try {
    const [rows]: any = await pool.query('SELECT * FROM nb_users WHERE api_token = ?', [token]);
    if (rows.length === 0) return null;
    return rows[0];
  } catch (error) {
    console.error('Error authenticating user from token:', error);
    return null;
  }
}
