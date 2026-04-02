import {
  DynamoDBClient
} from '@aws-sdk/client-dynamodb';
import {
  DynamoDBDocumentClient,
  ScanCommand,
  DeleteCommand
} from '@aws-sdk/lib-dynamodb';
import {
  ApiGatewayManagementApiClient,
  PostToConnectionCommand
} from '@aws-sdk/client-apigatewaymanagementapi';

const ddbClient = new DynamoDBClient({});
const ddbDocClient = DynamoDBDocumentClient.from(ddbClient);

// Sleep helper
const sleep = (ms) => new Promise(resolve => setTimeout(resolve, ms));

export const notifyAllClients = async (message) => {
  const endpoint = `${process.env.WEBSOCKET_ENDPOINT}`;
  const tableName = process.env.WEBSOCKET_TABLE_NAME;

  if (!endpoint || !tableName) {
    console.error("Faltan variables de entorno WEBSOCKET_ENDPOINT o WEBSOCKET_TABLE_NAME");
    return;
  }
  
  const apiClient = new ApiGatewayManagementApiClient({ endpoint: `https://${endpoint}` });

  try {
    const result = await ddbDocClient.send(new ScanCommand({
      TableName: tableName
    }));

    const connections = result.Items || [];

    const now = new Date();
    message = {
      ...message,
      date: now.toISOString().split('T')[0],            // Ej: "2025-05-15"
      time: now.toISOString().split('T')[1].slice(0, 8) // Ej: "14:30:45"
    };

    for (const { connectionId } of connections) {
      const data = { message, connections };

      try {
        await apiClient.send(
          new PostToConnectionCommand({
            ConnectionId: connectionId,
            Data: JSON.stringify(data)
          })
        );
      } catch (err) {
        if (err.name === 'GoneException') {
          await ddbDocClient.send(new DeleteCommand({
            TableName: tableName,
            Key: { connectionId }
          }));
          console.error(`Conexión muerta eliminada: ${connectionId}`);
        } else {
          console.error(`Error con ${connectionId}:`, err);
        }
      }

      // Esperar 50 ms entre cada envío
      await sleep(50);
    }

    console.log("Mensaje enviado a todos los clientes WebSocket");
    console.log("Message:", message);
  } catch (err) {
    console.error("Error notificando a los clientes WebSocket:", err);
  }
};