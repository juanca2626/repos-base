interface CatalogItem {
  code: string;
  name: string;
}

export function resolveNames(selectedIds: string[], catalog: CatalogItem[]): string[] {
  if (!selectedIds?.length) return [];

  const map = new Map(catalog.map((item) => [item.code, item.name]));

  return selectedIds
    .map((id) => map.get(id))
    .filter(Boolean)
    .map(cleanName);
}

function cleanName(name?: string): string {
  if (!name) return '';
  return name.replace(/^\(.*?\)\s*/, '');
}
