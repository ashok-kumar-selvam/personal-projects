export default interface Timer {
  time: number;
  limit: number;
  intervalId: NodeJS.Timeout | null;
}