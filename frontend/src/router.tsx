import { createBrowserRouter } from 'react-router';
import { AppLayout } from './components/layout/AppLayout';
import { PersonRoute } from './components/PersonRoute';
import { LoginPage } from './pages/auth/LoginPage';
import { RegisterPage } from './pages/auth/RegisterPage';
import { SelectPersonPage } from './pages/auth/SelectPersonPage';
import { ResetPasswordPage } from './pages/auth/ResetPasswordPage';
import { VerifyEmailPage } from './pages/auth/VerifyEmailPage';
import { HomePage } from './pages/HomePage';

export const router = createBrowserRouter([
  {
    element: <AppLayout />,
    children: [
      { path: '/', element: <HomePage /> },
      { path: '/login', element: <LoginPage /> },
      { path: '/register', element: <RegisterPage /> },
      { path: '/select-person', element: <SelectPersonPage /> },
      { path: '/reset-password', element: <ResetPasswordPage /> },
      { path: '/verify-email', element: <VerifyEmailPage /> },
      {
        path: '/tree',
        lazy: () => import('./pages/tree/TreePage').then((m) => ({ Component: m.TreePage })),
      },
      {
        path: '/about',
        lazy: () => import('./pages/about/AboutPage').then((m) => ({ Component: m.AboutPage })),
      },
      {
        path: '/person/:id',
        lazy: () => import('./pages/person/PersonPage').then((m) => ({ Component: m.PersonPage })),
      },
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
    ],
  },
]);
