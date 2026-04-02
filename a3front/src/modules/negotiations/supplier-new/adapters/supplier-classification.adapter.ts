export interface SupplierClassificationApiResponse {
  success: boolean;
  data: {
    classifications: Array<{
      code: string;
      name: string;
    }>;
    subClassifications: Array<{
      typeCode: string;
      name: string;
      classificationCode: string;
      subtypes: Array<{
        subtypeCode: string;
        name: string;
      }>;
    }>;
    typeCodes: Record<string, string>;
  };
}

export interface NormalizedClassification {
  typeCode: string;
  name: string;
}

export interface NormalizedSubClassification {
  subtypeCode: string;
  name: string;
  parentTypeCode: string;
  /** true cuando proviene de Strategy B (grupo con múltiples subs, ej: TRP → AER, ACU, TRP, TRN) */
  isDirectSub: boolean;
}

export interface NormalizedSubSubClassification {
  subtypeCode: string;
  name: string;
  parentSubtypeCode: string;
}

export interface NormalizedClassificationsCatalog {
  classifications: NormalizedClassification[];
  subClassifications: NormalizedSubClassification[];
  subSubClassifications: NormalizedSubSubClassification[];
}

export function adaptClassificationsCatalog(
  apiResponse: SupplierClassificationApiResponse
): NormalizedClassificationsCatalog {
  if (!apiResponse?.success || !apiResponse?.data) {
    return {
      classifications: [],
      subClassifications: [],
      subSubClassifications: [],
    };
  }

  const { classifications, subClassifications } = apiResponse.data;

  // Transform classifications: code -> typeCode
  const normalizedClassifications: NormalizedClassification[] = classifications.map((c) => ({
    typeCode: c.code,
    name: c.name,
  }));

  // Transform subClassifications with two strategies depending on the parent group:
  //
  // A) Single subClassification with subtypes (ATT, RES, STA, ...):
  //    → Show the subtypes directly (e.g. "Museo/Iglesia", "Restaurante", "Staff freelance")
  //
  // B) Multiple subClassifications under the same parent (TRP: AER, ACU, TRP, TRN)
  //    OR a single one with no subtypes (MIS: OTR):
  //    → Show the subClassifications themselves as options.
  //      Entries without subtypes (Lanchas, Trenes) are included as-is using their own typeCode.
  const normalizedSubClassifications: NormalizedSubClassification[] = [];
  const normalizedSubSubClassifications: NormalizedSubSubClassification[] = [];

  // Group by classificationCode to determine which strategy to use per parent
  const subsByParent = new Map<string, typeof subClassifications>();
  subClassifications.forEach((sub) => {
    const group = subsByParent.get(sub.classificationCode) ?? [];
    group.push(sub);
    subsByParent.set(sub.classificationCode, group);
  });

  subsByParent.forEach((subs, classificationCode) => {
    // Strategy A: único sub con subtypes → aplanar sus subtypes como segundo nivel
    // EXCEPCIONES: Staff (STA), Restaurante (RES) y Atractivos turísticos (ATT)
    // deben mantener la estructura de 2 niveles
    const shouldUseStrategyB =
      classificationCode === 'STA' || classificationCode === 'RES' || classificationCode === 'ATT';

    if (subs.length === 1 && subs[0].subtypes.length > 0 && !shouldUseStrategyB) {
      subs[0].subtypes.forEach((subtype) => {
        normalizedSubClassifications.push({
          subtypeCode: subtype.subtypeCode,
          name: subtype.name,
          parentTypeCode: classificationCode,
          isDirectSub: false,
        });
      });
    } else {
      // Strategy B: múltiples subs (o único sin subtypes, o Staff) → usar subs como segundo nivel.
      // isDirectSub=true indica que este sub es el "tipo" visible para el usuario (ej: TRN, ACU).
      // Si un sub tiene subtypes propios (ej: AER → DOM/INT, TRP → RUT/TUR/INP/MAL),
      // esos subtypes se almacenan en subSubClassifications como tercer nivel.
      // EXCEPCIÓN: si el sub tiene un solo subtype con el mismo código (ej: TRN → TRN),
      // NO se agrega a subSubClassifications porque es redundante.
      subs.forEach((sub) => {
        normalizedSubClassifications.push({
          subtypeCode: sub.typeCode,
          name: sub.name,
          parentTypeCode: classificationCode,
          isDirectSub: true,
        });

        // Solo agregar subtypes si hay más de uno O si el único es diferente al typeCode del padre
        const shouldAddSubtypes =
          sub.subtypes.length > 1 ||
          (sub.subtypes.length === 1 && sub.subtypes[0].subtypeCode !== sub.typeCode);

        if (shouldAddSubtypes) {
          sub.subtypes.forEach((subtype) => {
            normalizedSubSubClassifications.push({
              subtypeCode: subtype.subtypeCode,
              name: subtype.name,
              parentSubtypeCode: sub.typeCode,
            });
          });
        }
      });
    }
  });

  return {
    classifications: normalizedClassifications,
    subClassifications: normalizedSubClassifications,
    subSubClassifications: normalizedSubSubClassifications,
  };
}
