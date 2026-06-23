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

    const toPublicImageUrl = (path: string): string => {
      if (!path) return '';
      if (/^https?:\/\//i.test(path)) return path;
      return path.startsWith('/') ? path : `/${path}`;
    };

    // Parse image JSON for each property
    const formattedListings = rows.map((p: any) => {
      let imagesList: string[] = [];
      if (p.images) {
        try {
          const parsed = JSON.parse(p.images);
          imagesList = Array.isArray(parsed) ? parsed : [];
        } catch {
          imagesList = [];
        }
      }
      const image_urls = imagesList.map(toPublicImageUrl).filter(Boolean);
      return {
        ...p,
        images: imagesList,
        image_urls,
        thumbnail_url: image_urls[0] || null,
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
