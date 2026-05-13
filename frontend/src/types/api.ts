export type Result<T, E = ApiError> = { ok: true; data: T } | { ok: false; error: E };

export interface ApiError {
  code: ErrorCode;
  message: string;
  violations: Record<string, string[]>;
}

export type ErrorCode =
  | 'PERSON_NOT_FOUND'
  | 'SPONSOR_NOT_FOUND'
  | 'CONTACT_NOT_FOUND'
  | 'USER_NOT_FOUND'
  | 'INVALID_EMAIL_FORMAT'
  | 'PERSON_ALREADY_HAS_ACCOUNT'
  | 'VALIDATION_ERROR'
  | 'UNAUTHORIZED'
  | 'FORBIDDEN'
  | 'INTERNAL_ERROR';
