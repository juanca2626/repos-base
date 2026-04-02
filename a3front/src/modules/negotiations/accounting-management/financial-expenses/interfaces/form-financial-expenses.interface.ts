export interface FormFinancialExpensesInterface {
  id?: number | null;
  date: [string | null, string | null];
  amount_value: number;
  type_amount: 'AMOUNT' | 'PERCENTAGE';
  method: string;
}
