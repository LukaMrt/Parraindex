export const UNIVERSITY_EMAIL_REGEX =
  /^[a-zA-Z-]+\.[a-zA-Z-]+@(?:etu\.univ-lyon1\.fr|cpe\.fr|insa-lyon\.fr)$/;

export function isUniversityEmail(email: string): boolean {
  return UNIVERSITY_EMAIL_REGEX.test(email);
}
