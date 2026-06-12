import { NextResponse } from 'next/server';
import { pool } from '../../../../lib/db';

export async function GET(
  request: Request,
  { params }: { params: Promise<{ id: string }> }
) {
  const { id: idOrSlug } = await params;

  try {
    let query = `
      SELECT 
        p.*, 
        c.name as city_name, 
        u.name as owner_name,
        u.email as owner_email,
        u.phone as owner_phone
      FROM nb_properties p 
      LEFT JOIN nb_cities c ON p.city_id = c.id 
      LEFT JOIN nb_users u ON p.owner_id = u.id 
      WHERE 
    `;
    const args = [];

    if (isNaN(Number(idOrSlug))) {
      query += 'p.slug = ?';
      args.push(idOrSlug);
    } else {
      query += 'p.id = ?';
      args.push(Number(idOrSlug));
    }

    const [rows]: any = await pool.query(query, args);

    if (rows.length === 0) {
      return NextResponse.json(
        { success: false, message: 'Property not found' },
        { status: 404 }
      );
    }

    const prop = rows[0];

    // Format list image URLs and JSON lists
    let imagesList = [];
    if (prop.images) {
      try {
        imagesList = JSON.parse(prop.images);
      } catch {
        imagesList = [];
      }
    }
    const imageUrls = imagesList.map((img: string) => {
      if (img.startsWith('http://') || img.startsWith('https://')) return img;
      return `/${img}`;
    });

    let amenitiesList = [];
    if (prop.amenities) {
      try {
        amenitiesList = JSON.parse(prop.amenities);
      } catch {
        amenitiesList = [];
      }
    }

    const propertyDetails = {
      ...prop,
      images: imagesList,
      image_urls: imageUrls,
      thumbnail_url: imageUrls[0] || null,
      amenities: amenitiesList,
    };

    return NextResponse.json({
      success: true,
      property: propertyDetails,
    });
  } catch (error: any) {
    console.error('Error fetching property details API:', error);
    return NextResponse.json(
      { success: false, message: error.message || 'Internal server error' },
      { status: 500 }
    );
  }
}
