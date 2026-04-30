import { createBrowserRouter } from 'react-router';
import { AppLayout } from './components/layout/AppLayout';
import { ProtectedRoute } from './components/ProtectedRoute';
import { LoginPage } from './pages/auth/LoginPage';
import { RegisterPage } from './pages/auth/RegisterPage';
import { CheckEmailPage } from './pages/auth/CheckEmailPage';
import { ResetPasswordPage } from './pages/auth/ResetPasswordPage';
import { HomePage } from './pages/HomePage';

export const router = createBrowserRouter([
  // Auth pages — pas de AppLayout
  { path: '/login', element: <LoginPage /> },
  { path: '/register', element: <RegisterPage /> },
  { path: '/check-email', element: <CheckEmailPage /> },
  { path: '/reset-password', element: <ResetPasswordPage /> },

  // Pages avec AppLayout
  {
    element: <AppLayout />,
    children: [
      // Pages publiques
      { path: '/', element: <HomePage /> },
      {
        path: '/tree',
        lazy: () => import('./pages/tree/TreePage').then((m) => ({ Component: m.TreePage })),
      },
      {
        path: '/about',
        lazy: () => import('./pages/about/AboutPage').then((m) => ({ Component: m.AboutPage })),
      },
      {
        path: '/mentions-legales',
        lazy: () =>
          import('./pages/legal/LegalNoticePage').then((m) => ({ Component: m.LegalNoticePage })),
      },
      {
        path: '/contact',
        lazy: () =>
          import('./pages/contact/ContactPage').then((m) => ({ Component: m.ContactPage })),
      },
      {
        path: '/personne/:id',
        lazy: () => import('./pages/person/PersonPage').then((m) => ({ Component: m.PersonPage })),
      },
      {
        path: '/parrainage/:id',
        lazy: () =>
          import('./pages/sponsor/SponsorPage').then((m) => ({ Component: m.SponsorPage })),
      },

      // Pages protégées (auth requise)
      {
        element: <ProtectedRoute />,
        children: [
          {
            path: '/personne/:id/modifier',
            lazy: () =>
              import('./pages/person/EditPersonPage').then((m) => ({
                Component: m.EditPersonPage,
              })),
          },
          {
            path: '/parrainage/:id/modifier',
            lazy: () =>
              import('./pages/sponsor/EditSponsorPage').then((m) => ({
                Component: m.EditSponsorPage,
              })),
          },
          {
            path: '/admin/contacts',
            lazy: () =>
              import('./pages/admin/AdminContactsPage').then((m) => ({
                Component: m.AdminContactsPage,
              })),
          },
        ],
      },
    ],
  },
]);
