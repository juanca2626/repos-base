export function createCustomerAdapter(customer) {
  return {
    id: customer.code,
    code: customer.client_code,
    name: customer.label.toUpperCase(),
  };
}
