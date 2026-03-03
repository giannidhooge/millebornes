export interface Card {
  type: 'distance' | 'speed' | 'hazard' | 'safety';
  subtype: string;
  value: number;
  label: string;
}