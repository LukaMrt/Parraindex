/**
 * Narrow `T | undefined` to `T` at runtime, throwing if undefined.
 * Useful in tests after `.find()` calls where we expect the value to exist
 * (and want a clear error message instead of `!.foo` non-null assertions).
 */
export function assertDefined<T>(value: T | undefined | null, message: string): T {
  if (value === undefined || value === null) {
    throw new Error(`Expected value to be defined: ${message}`);
  }
  return value;
}
