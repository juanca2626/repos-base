export const createClientAdapter = (client) => ({
  id: client.id,
  value: client.code,
  label: `${client.code} - ${client.name}`,
  haveCredit: client.have_credit,
  creditLine: client.credit_line,
});
