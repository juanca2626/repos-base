export async function savePriceStep(state: any) {
  await new Promise((resolve) => setTimeout(resolve, 800));

  console.log('savePriceStep', state);
  return true;
}
