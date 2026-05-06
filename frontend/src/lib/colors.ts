export const PROMO_PALETTE = [
  '#48BFA0', // menthe
  '#E85D75', // rose corail
  '#F4A236', // ambre
  '#6C63FF', // violet
  '#2196F3', // bleu
  '#8BC34A', // vert lime
  '#2E4057', // bleu ardoise
  '#FF7043', // orange brûlé
  '#26C6DA', // cyan
] as const;

const BASE_YEAR = 2019;

export function promoColor(startYear: number): string {
  const idx =
    (((startYear - BASE_YEAR) % PROMO_PALETTE.length) + PROMO_PALETTE.length) %
    PROMO_PALETTE.length;
  return PROMO_PALETTE[idx];
}

/** Hex couleur → couleur de texte lisible (#000 ou #fff) */
export function contrastColor(hex: string): '#000000' | '#ffffff' {
  const h = hex.replace('#', '').padEnd(6, '0');
  const n = parseInt(h.slice(0, 6), 16);
  const r = (n >> 16) & 255,
    g = (n >> 8) & 255,
    b = n & 255;
  return r * 299 + g * 587 + b * 114 > 148000 ? '#000000' : '#ffffff';
}
