import { describe, it, expect, vi } from 'vitest';
import { fetchContentUseCase } from '../content/fetchContent.useCase';

import * as serviceResolver from '../../application/content/contentServiceResolver';
import * as strategyResolver from '../../domain/content/contentStrategyResolver';

describe('fetchContentUseCase', () => {
  it('should fetch and transform GENERIC_LOADING content', async () => {
    const mockService = vi.fn().mockResolvedValue({
      data: {
        texts: [
          {
            textTypeCode: 'DESCRIPTION',
            html: '<p>Hello</p>',
            status: 'ACTIVE',
          },
        ],
        inclusions: [],
        requirements: [],
        contentOperability: {
          items: [],
        },
      },
    });

    const mockStrategy = vi.fn().mockReturnValue({
      texts: [
        {
          textTypeCode: 'DESCRIPTION',
          html: '<p>Hello</p>',
          status: 'ACTIVE',
        },
      ],
    });

    vi.spyOn(serviceResolver, 'resolveContentService').mockReturnValue(mockService);

    vi.spyOn(strategyResolver, 'resolveContentStrategy').mockReturnValue(mockStrategy);

    const result = await fetchContentUseCase(
      'GENERIC',
      'LOADING',
      '69946ad10fd893866808e782',
      '69946b1e0fd893866808e7a7'
    );

    expect(mockService).toHaveBeenCalled();
    expect(mockStrategy).toHaveBeenCalled();

    expect(result.texts.length).toBe(1);
    expect(result.texts[0].textTypeCode).toBe('DESCRIPTION');
  });

  it('should fetch and transform TRAIN_LOADING content', async () => {
    const mockService = vi.fn().mockResolvedValue({
      data: {
        text: {
          textTypeCode: 'DESCRIPTION',
          html: '<p>Train</p>',
          status: 'ACTIVE',
        },
        inclusions: [
          {
            inclusionCode: 'MEAL',
            included: 'Lunch',
            visibleToClient: true,
          },
        ],
        requirements: [
          {
            requirementCode: 'PASSPORT',
            visibleToClient: true,
          },
        ],
      },
    });

    const mockStrategy = vi.fn().mockReturnValue({
      text: {
        textTypeCode: 'DESCRIPTION',
        html: '<p>Train</p>',
        status: 'ACTIVE',
      },
      inclusions: [
        {
          inclusionCode: 'MEAL',
          included: 'Lunch',
          visibleToClient: true,
        },
      ],
      requirements: [
        {
          requirementCode: 'PASSPORT',
          visibleToClient: true,
        },
      ],
    });

    vi.spyOn(serviceResolver, 'resolveContentService').mockReturnValue(mockService);

    vi.spyOn(strategyResolver, 'resolveContentStrategy').mockReturnValue(mockStrategy);

    const result = await fetchContentUseCase(
      'TRAIN',
      'LOADING',
      '69946ef40fd893866808e824',
      '69946f370fd893866808e84b'
    );

    expect(result.text).toBeDefined();
    expect(result.text.textTypeCode).toBe('DESCRIPTION');
    expect(result.inclusions?.length).toBe(1);
    expect(result.requirements?.length).toBe(1);

    expect(result.inclusions?.[0].inclusionCode).toBe('MEAL');
  });

  it('should fetch and transform PACKAGE_LOADING content', async () => {
    const mockService = vi.fn().mockResolvedValue({
      data: {
        texts: [
          {
            textTypeCode: 'DESCRIPTION',
            html: '<p>Package</p>',
            status: 'ACTIVE',
          },
        ],
        contentOperability: {
          items: [
            {
              scheduleId: 'schedule-1',
              days: [],
            },
          ],
        },
      },
    });

    const mockStrategy = vi.fn().mockReturnValue({
      texts: [
        {
          textTypeCode: 'DESCRIPTION',
          html: '<p>Package</p>',
          status: 'ACTIVE',
        },
      ],
      contentOperability: {
        items: [
          {
            scheduleId: 'schedule-1',
            days: [],
          },
        ],
      },
    });

    vi.spyOn(serviceResolver, 'resolveContentService').mockReturnValue(mockService);

    vi.spyOn(strategyResolver, 'resolveContentStrategy').mockReturnValue(mockStrategy);

    const result = await fetchContentUseCase(
      'PACKAGE',
      'LOADING',
      '699473730fd893866808e955',
      '6994752b0fd893866808ea28'
    );

    expect(result.texts.length).toBe(1);
    expect(result.texts[0].textTypeCode).toBe('DESCRIPTION');
    expect(result.contentOperability?.items?.length).toBe(1);
    expect(result.contentOperability?.items?.[0].scheduleId).toBe('schedule-1');
  });
});
