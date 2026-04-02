export interface MenuItemState {
  currentKey: string;
  currentCode: string;
  itemId: string;
  completed: boolean;
  data?: Record<string, unknown>;
}

export type MenuItemKey = `${string}-${string}-${string}`; // currentKey-currentCode-itemId
