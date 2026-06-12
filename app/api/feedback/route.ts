import { NextResponse } from 'next/server';
import { getAuthUser } from '../../../lib/auth';
import { pool } from '../../../lib/db';
import { writeFile, mkdir } from 'fs/promises';
import path from 'path';

export async function GET(request: Request) {
  const user = await getAuthUser(request);

  if (!user) {
    return NextResponse.json(
      { success: false, message: 'Unauthorized' },
      { status: 401 }
    );
  }

  try {
    const [rows]: any = await pool.query(
      'SELECT * FROM feedbacks WHERE userId = ? ORDER BY createdAt DESC',
      [user.id]
    );

    return NextResponse.json({
      success: true,
      feedbacks: rows,
    });
  } catch (error: any) {
    console.error('Error fetching user feedbacks:', error);
    return NextResponse.json(
      { success: false, message: error.message || 'Internal server error' },
      { status: 500 }
    );
  }
}

export async function POST(request: Request) {
  const user = await getAuthUser(request);

  if (!user) {
    return NextResponse.json(
      { success: false, message: 'Unauthorized' },
      { status: 401 }
    );
  }

  try {
    const contentType = request.headers.get('content-type') || '';
    let title = '';
    let description = '';
    let name = user.fullname || user.name || '';
    let imageUrl: string | null = null;

    if (contentType.includes('multipart/form-data')) {
      const formData = await request.formData();
      title = formData.get('title') as string || '';
      description = formData.get('description') as string || '';
      if (formData.get('name')) {
        name = formData.get('name') as string;
      }

      const file = formData.get('image_file') as File | null;
      if (file && file.size > 0) {
        const bytes = await file.arrayBuffer();
        const buffer = Buffer.from(bytes);

        const uploadDir = path.join(process.cwd(), 'public', 'uploads', 'feedbacks');
        await mkdir(uploadDir, { recursive: true });

        const filename = `${Date.now()}_${file.name.replace(/\s+/g, '_')}`;
        const filePath = path.join(uploadDir, filename);
        await writeFile(filePath, buffer);
        imageUrl = `uploads/feedbacks/${filename}`;
      }
    } else {
      const body = await request.json();
      title = body.title || '';
      description = body.description || '';
      name = body.name || user.fullname || user.name || '';
      imageUrl = body.image || null;
    }

    if (!title.trim()) {
      return NextResponse.json(
        { success: false, message: 'Subject/title is required' },
        { status: 400 }
      );
    }

    await pool.query(
      'INSERT INTO feedbacks (userId, title, description, image, name, createdAt) VALUES (?, ?, ?, ?, ?, NOW())',
      [user.id, title, description, imageUrl, name]
    );

    return NextResponse.json({
      success: true,
      message: 'Feedback submitted successfully!',
    });
  } catch (error: any) {
    console.error('Error saving user feedback:', error);
    return NextResponse.json(
      { success: false, message: error.message || 'Internal server error' },
      { status: 500 }
    );
  }
}
