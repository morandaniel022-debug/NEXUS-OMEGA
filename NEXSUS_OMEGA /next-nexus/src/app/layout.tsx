import type { Metadata } from 'next'
import { Inter } from 'next/font/google'
import { SessionProvider } from 'next-auth/react'
import './globals.css'
import './nexus-theme.css'
import { TabsProvider } from '@/hooks/use-tabs'

const inter = Inter({ subsets: ['latin'] })

export const metadata: Metadata = {
  title: 'NEXSUS Ω - Revenue System',
  description: 'Advanced AI Revenue Automation Platform',
}

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
        <link href="https://fonts.googleapis.com/css2?family=Rajdhani:wght@300;400;500;600;700&family=Orbitron:wght@400;500;600;700;800;900&display=swap" rel="stylesheet" />
      </head>
      <body className={inter.className}>
        <SessionProvider>
          <TabsProvider>
            {children}
          </TabsProvider>
        </SessionProvider>
      </body>
    </html>
  )
}
