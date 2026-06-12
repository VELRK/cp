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
    // 1. Fetch all listings posted by owner
    const [listings]: any = await pool.query(
      `SELECT * FROM nb_properties WHERE owner_id = ?`,
      [user.id]
    );

    const totalListings = listings.length;
    const activeListings = listings.filter((l: any) => Number(l.is_active) === 1).length;
    const totalViews = listings.reduce((sum: number, l: any) => sum + Number(l.views || 0), 0);

    // 2. Fetch properties IDs owned by user
    const pids = listings.map((l: any) => Number(l.id));

    let enquiryCount = 0;
    let recentEnquiries: any[] = [];

    if (pids.length > 0) {
      // 3. Count total enquiries on owner's properties
      const [countResult]: any = await pool.query(
        `SELECT COUNT(*) as cnt FROM nb_enquiries WHERE property_id IN (?)`,
        [pids]
      );
      enquiryCount = countResult[0].cnt;

      // 4. Fetch top 5 recent enquiries on owner's properties
      const [enquiriesResult]: any = await pool.query(
        `SELECT 
          e.*, 
          p.title AS property_title, 
          u.name AS tenant_name
         FROM nb_enquiries e
         JOIN nb_properties p ON p.id = e.property_id
         JOIN nb_users u ON u.id = e.tenant_id
         WHERE e.property_id IN (?)
         ORDER BY e.created_at DESC
         LIMIT 5`,
        [pids]
      );
      recentEnquiries = enquiriesResult;
    }

    return NextResponse.json({
      success: true,
      stats: {
        total_listings: totalListings,
        active_listings: activeListings,
        total_views: totalViews,
        enquiry_count: enquiryCount,
      },
      recent_enquiries: recentEnquiries,
    });
  } catch (error: any) {
    console.error('Error loading owner dashboard stats:', error);
    return NextResponse.json(
      { success: false, message: error.message || 'Internal server error' },
      { status: 500 }
    );
  }
}
