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
        p.*, 
        c.name AS city_name
       FROM nb_properties p
       LEFT JOIN nb_cities c ON c.id = p.city_id
       WHERE p.owner_id = ?
       ORDER BY p.created_at DESC`,
      [user.id]
    );

    // Parse image JSON for each property
    const formattedListings = rows.map((p: any) => {
      let imagesList = [];
      if (p.images) {
        try {
          imagesList = JSON.parse(p.images);
        } catch {
          imagesList = [];
        }
      }
      return {
        ...p,
        images: imagesList,
        thumbnail_url: imagesList[0] ? `/${imagesList[0]}` : null,
      };
    });

    return NextResponse.json({
      success: true,
      listings: formattedListings,
    });
  } catch (error: any) {
    console.error('Error fetching owner listings:', error);
    return NextResponse.json(
      { success: false, message: error.message || 'Internal server error' },
      { status: 500 }
    );
  }
}
