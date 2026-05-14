import { Component } from 'react';
import type { ReactNode, ErrorInfo } from 'react';

interface Props {
  children: ReactNode;
}

interface State {
  error: Error | null;
}

export class ErrorBoundary extends Component<Props, State> {
  constructor(props: Props) {
    super(props);
    this.state = { error: null };
  }

  static getDerivedStateFromError(error: Error): State {
    return { error };
  }

  componentDidCatch(error: Error, info: ErrorInfo) {
    console.error('[ErrorBoundary]', error, info.componentStack);
  }

  render() {
    if (this.state.error !== null) {
      return (
        <div className="flex min-h-screen items-center justify-center bg-bg px-6">
          <div className="max-w-md rounded-2xl border border-line bg-surface p-8 text-center shadow-sm">
            <div className="mb-4 text-4xl">⚠️</div>
            <h1 className="mb-2 text-[20px] font-semibold text-ink">
              Une erreur inattendue s&apos;est produite
            </h1>
            <p className="mb-5 text-[14px] leading-relaxed text-ink-2">
              L&apos;application a rencontré un problème. Si l&apos;erreur persiste, contactez un
              administrateur en décrivant ce que vous faisiez.
            </p>
            <button
              onClick={() => {
                window.location.href = '/';
              }}
              className="inline-flex h-9 items-center justify-center rounded-[9px] bg-ink px-5 text-sm font-medium text-white transition-opacity hover:opacity-90"
            >
              Retour à l&apos;accueil
            </button>
          </div>
        </div>
      );
    }

    return this.props.children;
  }
}
