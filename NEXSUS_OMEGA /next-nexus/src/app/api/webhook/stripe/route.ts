import { NextRequest, NextResponse } from 'next/server';
import { headers } from 'next/server';
import { stripe } from '@/lib/stripe';
import { prisma } from '@/lib/db';

const endpointSecret = process.env.STRIPE_WEBHOOK_SECRET!;

export async function POST(request: NextRequest) {
  try {
    const body = await request.text();
    const sig = headers().get('stripe-signature');

    let event;

    try {
      event = stripe.webhooks.constructEvent(body, sig!, endpointSecret);
    } catch (err: any) {
      console.error(`Webhook signature verification failed.`, err.message);
      return NextResponse.json({ error: 'Webhook error' }, { status: 400 });
    }

    // Handle the event
    switch (event.type) {
      case 'customer.subscription.created':
      case 'customer.subscription.updated':
        const subscription = event.data.object;
        await handleSubscriptionChange(subscription);
        break;

      case 'customer.subscription.deleted':
        await handleSubscriptionDeletion(event.data.object);
        break;

      case 'invoice.payment_succeeded':
        await handlePaymentSuccess(event.data.object);
        break;

      case 'invoice.payment_failed':
        await handlePaymentFailure(event.data.object);
        break;

      default:
        console.log(`Unhandled event type ${event.type}`);
    }

    return NextResponse.json({ received: true });
  } catch (error) {
    console.error('Webhook error:', error);
    return NextResponse.json({ error: 'Internal server error' }, { status: 500 });
  }
}

async function handleSubscriptionChange(subscription: any) {
  const subscriptionData = {
    status: subscription.status,
    currentPeriodStart: new Date(subscription.current_period_start * 1000),
    currentPeriodEnd: new Date(subscription.current_period_end * 1000),
    cancelAtPeriodEnd: subscription.cancel_at_period_end,
  };

  await prisma.subscription.update({
    where: { stripeId: subscription.id },
    data: subscriptionData,
  });
}

async function handleSubscriptionDeletion(subscription: any) {
  await prisma.subscription.update({
    where: { stripeId: subscription.id },
    data: { status: 'canceled' },
  });
}

async function handlePaymentSuccess(invoice: any) {
  // Record successful payment
  await prisma.payment.create({
    data: {
      userId: invoice.customer_email, // We'll need to map this properly
      amount: invoice.amount_due / 100, // Convert from cents
      currency: invoice.currency,
      status: 'completed',
      stripeId: invoice.id,
      description: 'Subscription payment',
    },
  });
}

async function handlePaymentFailure(invoice: any) {
  // Handle failed payment
  await prisma.payment.create({
    data: {
      userId: invoice.customer_email, // We'll need to map this properly
      amount: invoice.amount_due / 100,
      currency: invoice.currency,
      status: 'failed',
      stripeId: invoice.id,
      description: 'Failed subscription payment',
    },
  });
}
