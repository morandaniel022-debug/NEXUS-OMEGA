import { NextRequest, NextResponse } from 'next/server';
import { cryptoAPI } from '@/lib/crypto-api';

export async function GET(request: NextRequest) {
  try {
    const marketData = await cryptoAPI.getMarketData();

    return NextResponse.json({
      success: true,
      data: marketData
    });
  } catch (error) {
    console.error('Crypto API Error:', error);
    return NextResponse.json(
      { success: false, error: 'Failed to fetch crypto data' },
      { status: 500 }
    );
  }
}
