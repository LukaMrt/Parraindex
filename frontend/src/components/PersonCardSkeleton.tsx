export function PersonCardSkeleton() {
  return (
    <article className="flex w-48 shrink-0 flex-col overflow-hidden rounded-2xl bg-white shadow">
      <div className="h-1 w-full animate-pulse bg-light-grey" />
      <div className="h-44 animate-pulse bg-light-grey" />
      <div className="flex flex-col gap-2 p-4">
        <div className="h-3 w-3/4 animate-pulse rounded bg-light-grey" />
        <div className="h-3 w-1/2 animate-pulse rounded bg-light-grey" />
        <div className="mt-1 h-2 w-1/4 animate-pulse rounded bg-light-grey" />
      </div>
    </article>
  );
}
