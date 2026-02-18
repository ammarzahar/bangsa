import "fastify";

declare module "fastify" {
  interface FastifyInstance {
    authenticate: (request: FastifyRequest, reply: FastifyReply) => Promise<void>;
  }

  interface FastifyRequest {
    authUserId?: string;
  }
}

declare module "@fastify/jwt" {
  interface FastifyJWT {
    payload: {
      userId: string;
      email: string;
      isPlatformOwner: boolean;
    };
    user: {
      userId: string;
      email: string;
      isPlatformOwner: boolean;
    };
  }
}