import './globals.css';
import Navbar from '../components/Navbar';
import { LanguageProvider } from '../components/LanguageProvider';

export const metadata = {
  title: 'Exam Result Management',
  description: 'Search and verify exam results securely'
};

export default function RootLayout({ children }) {
  return (
    <html lang="en">
      <body>
        <LanguageProvider>
          <main className="max-w-6xl mx-auto p-4 space-y-4">
            <Navbar />
            {children}
          </main>
        </LanguageProvider>
      </body>
    </html>
  );
}
