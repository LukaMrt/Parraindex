import { createBrowserRouter } from 'react-router';
import { AppLayout } from './components/layout/AppLayout';
import { AdminRoute } from './components/AdminRoute';
import { PersonRoute } from './components/PersonRoute';
import { LoginPage } from './pages/auth/LoginPage';
import { RegisterPage } from './pages/auth/RegisterPage';
import { CheckEmailPage } from './pages/auth/CheckEmailPage';
import { ResetPasswordPage } from './pages/auth/ResetPasswordPage';
import { HomePage } from './pages/HomePage';

export const router = createBrowserRouter([
  // Pages avec AppLayout
  {
    element: <AppLayout />,
    children: [
      // Pages publiques
      { path: '/', element: <HomePage /> },
      { path: '/login', element: <LoginPage /> },
      { path: '/register', element: <RegisterPage /> },
      { path: '/check-email', element: <CheckEmailPage /> },
      { path: '/reset-password', element: <ResetPasswordPage /> },
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
        path: '/person/:id',
        lazy: () => import('./pages/person/PersonPage').then((m) => ({ Component: m.PersonPage })),
      },
      // Pages protégées : même personne ou admin
      {
        element: <PersonRoute />,
        children: [
          {
            path: '/person/:id/edit',
            lazy: () =>
              import('./pages/person/EditPersonPage').then((m) => ({
                Component: m.EditPersonPage,
              })),
          },
        ],
      },
      // Pages protégées : admin uniquement
      {
        element: <AdminRoute />,
        children: [
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
