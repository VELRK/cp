import { NextResponse } from 'next/server';
import { getAuthUser } from '../../../../lib/auth';
import { pool } from '../../../../lib/db';

export async function GET(request: Request) {
  const user = await getAuthUser(request);

  if (!user || user.role !== 'owner') {
    return NextResponse.json(
      { success: false, message: 'Unauthorized' },
      { status: 401 }
    );
  }

  try {
    const [rows]: any = await pool.query(
      `SELECT 
        e.*, 
        p.title AS property_title, 
        u.name AS tenant_name
       FROM nb_enquiries e
       JOIN nb_properties p ON p.id = e.property_id
       JOIN nb_users u ON u.id = e.tenant_id
       WHERE p.owner_id = ?
       ORDER BY e.created_at DESC`,
      [user.id]
    );

    return NextResponse.json({
      success: true,
      enquiries: rows,
    });
  } catch (error: any) {
    console.error('Error fetching owner enquiries:', error);
    return NextResponse.json(
      { success: false, message: error.message || 'Internal server error' },
      { status: 500 }
    );
  }
}
